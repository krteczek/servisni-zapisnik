<?php
declare(strict_types=1);

namespace App\Core;

class EvolvedRouter extends Router
{
    private static ?self $instance = null;
    private array $namedRoutes = [];
    private array $compiledRoutes = [];
    
    public static function getInstance(array $routes = []): self
    {
        if (self::$instance === null) {
            self::$instance = new self($routes);
        }
        return self::$instance;
    }
    
    // ZACHOVÁVÁME původní konstruktor pro backward compatibility
    public function __construct(array $routes)
    {
        parent::__construct($routes);
        $this->compileRoutes();
    }
    
    private function compileRoutes(): void
    {
        foreach ($this->routes as $index => $route) {
            // 1. Pojmenování rout (pro generování URL)
            if (isset($route['name'])) {
                $this->namedRoutes[$route['name']] = $index;
            }
            
            // 2. Kompilace patternů pro parametry
            if (str_contains($route['path'], '{')) {
                $this->compiledRoutes[$index] = $this->compilePattern($route['path']);
            }
        }
    }
    
    private function compilePattern(string $path): array
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        return [
            'regex' => '#^' . $pattern . '$#',
            'params' => $this->extractParamNames($path)
        ];
    }
    
    private function extractParamNames(string $path): array
    {
        preg_match_all('/\{(\w+)\}/', $path, $matches);
        return $matches[1] ?? [];
    }
    
    // PŘIDÁVÁME nové možnosti do původní metody dispatch
    public function dispatch(string $path, string $method): string
    {
        $normalizedPath = $this->normalizePath($path);
        
        // 1. Pokus o exact match (původní chování)
        foreach ($this->routes as $index => $route) {
            if ($route['method'] !== $method) continue;
            
            if ($route['path'] === $normalizedPath) {
                return $this->processRoute($route);
            }
            
            // 2. NOVÉ: Pokus o match s parametry
            if (isset($this->compiledRoutes[$index])) {
                $compiled = $this->compiledRoutes[$index];
                $matches = [];
                
                if (preg_match($compiled['regex'], $normalizedPath, $matches)) {
                    $params = [];
                    foreach ($compiled['params'] as $paramName) {
                        if (isset($matches[$paramName])) {
                            $params[$paramName] = $matches[$paramName];
                        }
                    }
                    
                    // Uložit parametry do route pro pozdější použití
                    $routeWithParams = $route;
                    $routeWithParams['_params'] = $params;
                    
                    return $this->processRoute($routeWithParams);
                }
            }
        }
        
        return parent::dispatch($path, $method); // Fallback na původní 404
    }
    
    // ROZŠIŘUJEME zpracování routy o parametry
    protected function call(array $action): string
    {
        [$controllerClass, $method] = $action;
        $controller = new $controllerClass();
        
        // Pokud máme parametry, předáme je do controlleru
        if (isset($this->currentRoute['_params'])) {
            // Metoda 1: Předat jako argumenty
            if (method_exists($controller, $method)) {
                $reflection = new \ReflectionMethod($controller, $method);
                $params = [];
                
                foreach ($reflection->getParameters() as $param) {
                    $name = $param->getName();
                    $params[] = $this->currentRoute['_params'][$name] ?? null;
                }
                
                return $controller->$method(...$params);
            }
        }
        
        // Původní chování
        return $controller->$method();
    }
    
    private $currentRoute = [];
    
    protected function processRoute(array $route): string
    {
        $this->currentRoute = $route;
        return parent::dispatch($route['path'], $route['method']);
    }
    
    // NOVÁ funkcionalita: Generování URL podle názvu
    public function generate(string $name, array $parameters = []): string
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new \InvalidArgumentException("Route '{$name}' not found");
        }
        
        $routeIndex = $this->namedRoutes[$name];
        $route = $this->routes[$routeIndex];
        $path = $route['path'];
        
        // Nahrazení parametrů
        foreach ($parameters as $key => $value) {
            $path = str_replace('{' . $key . '}', $value, $path);
        }
        
        // Validace, že všechny required parametry jsou nahrazeny
        if (str_contains($path, '{')) {
            preg_match_all('/\{(\w+)\}/', $path, $missing);
            throw new \InvalidArgumentException(
                "Missing parameters for route '{$name}': " . implode(', ', $missing[1])
            );
        }
        
        return $path;
    }
    
    // NOVÁ funkcionalita: Skupiny rout
    public function group(array $attributes, \Closure $callback): void
    {
        $prefix = $attributes['prefix'] ?? '';
        $middleware = $attributes['middleware'] ?? [];
        $auth = $attributes['auth'] ?? false;
        $roles = $attributes['roles'] ?? [];
        
        $originalRoutes = $this->routes;
        $callback($this);
        
        // Aplikovat skupinové atributy na nové routy
        $newRoutes = array_slice($this->routes, count($originalRoutes));
        
        foreach ($newRoutes as $index => $route) {
            if (!empty($prefix)) {
                $this->routes[$index]['path'] = rtrim($prefix, '/') . '/' . ltrim($route['path'], '/');
            }
            
            if ($auth) {
                $this->routes[$index]['auth'] = true;
            }
            
            if (!empty($roles)) {
                $this->routes[$index]['roles'] = array_merge(
                    $this->routes[$index]['roles'] ?? [],
                    $roles
                );
            }
        }
    }
    
    private function normalizePath(string $path): string
    {
        $path = rtrim($path, '/');
        return $path === '' ? '/' : $path;
    }
}