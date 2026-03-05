<?php
/**
 * Route Flow Analyzer pro vlastní MVC framework
 * Respektuje strukturu: app/Config/routes.php, app/Core/, app/Controllers/, app/Models/, app/Services/, app/Views/
 *
 * Použití: php route-analyzer.php [--format=html|md|both] [--controller=Název] [--db-only]
 */

class RouteFlowAnalyzer
{
    private array $routes = [];
    private array $controllers = [];
    private array $models = [];
    private array $services = [];
    private array $views = [];
    private array $databaseModels = [
        'db1' => [],
        'db2' => []
    ];
    private array $report = [];

    private string $projectRoot;
    private string $configDir;
    private string $coreDir;
    private string $controllersDir;
    private string $modelsDir;
    private string $servicesDir;
    private string $viewsDir;
    private string $baseModelClass = 'BaseModel';

    public function __construct(string $projectRoot = __DIR__)
    {
        $this->projectRoot = rtrim($projectRoot, '/');
        $this->configDir = $this->projectRoot . '/app/Config/';
        $this->coreDir = $this->projectRoot . '/app/Core/';
        $this->controllersDir = $this->projectRoot . '/app/Controllers/';
        $this->modelsDir = $this->projectRoot . '/app/Models/';
        $this->servicesDir = $this->projectRoot . '/app/Services/';
        $this->viewsDir = $this->projectRoot . '/app/Views/';

        $this->loadRoutes();
        $this->scanDirectories();
        $this->analyzeModelsDatabase();
    }

    /**
     * Načte routy z app/Config/routes.php
     */
    private function loadRoutes(): void
    {
        $routesFile = $this->configDir . 'routes.php';

        if (!file_exists($routesFile)) {
            die("Chyba: routes.php nenalezen v {$routesFile}\n");
        }

        $routes = require $routesFile;

        if (!is_array($routes)) {
            die("Chyba: routes.php musí vracet pole\n");
        }

        // Filtrujeme pouze platné routy
        $this->routes = array_filter($routes, function($route) {
            return isset($route['method'], $route['path'], $route['action']);
        });

        echo "📥 Načteno " . count($this->routes) . " rout z app/Config/routes.php\n";
    }

    /**
     * Prohledá adresáře s controllery, modely, services
     */
    private function scanDirectories(): void
    {
        // Controllery
        if (is_dir($this->controllersDir)) {
            $files = glob($this->controllersDir . '*.php');
            foreach ($files as $file) {
                $controller = basename($file, '.php');
                $this->controllers[$controller] = [
                    'file' => $file,
                    'name' => $controller,
                    'namespace' => $this->extractNamespace($file)
                ];
            }
            echo "📁 Nalezeno " . count($this->controllers) . " controllerů\n";
        }

        // Modely - včetně BaseModel
        if (is_dir($this->modelsDir)) {
            $files = glob($this->modelsDir . '*.php');
            foreach ($files as $file) {
                $model = basename($file, '.php');
                $this->models[$model] = [
                    'file' => $file,
                    'name' => $model,
                    'namespace' => $this->extractNamespace($file),
                    'extends' => $this->extractParentClass($file),
                    'db' => 'unknown'
                ];
            }

            // Hledáme i v podadresářích (kdyby byly)
            $this->scanModelsRecursive($this->modelsDir);

            echo "📁 Nalezeno " . count($this->models) . " modelů\n";
        }

        // Services
        if (is_dir($this->servicesDir)) {
            $files = glob($this->servicesDir . '*.php');
            foreach ($files as $file) {
                $service = basename($file, '.php');
                $this->services[$service] = [
                    'file' => $file,
                    'name' => $service,
                    'namespace' => $this->extractNamespace($file)
                ];
            }
            echo "📁 Nalezeno " . count($this->services) . " servis\n";
        }

        // Views
        if (is_dir($this->viewsDir)) {
            $this->scanViewsDirectory($this->viewsDir);
            echo "📁 Nalezeno " . count($this->views) . " view souborů\n";
        }

        // Core soubory
        if (is_dir($this->coreDir)) {
            $coreFiles = glob($this->coreDir . '*.php');
            echo "⚙️ Nalezeno " . count($coreFiles) . " core souborů (včetně Controller.php, Config.php)\n";
        }
    }

    /**
     * Rekurzivně prohledá adresář s modely
     */
    private function scanModelsRecursive(string $dir, string $prefix = ''): void
    {
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || $item === 'BaseModel.php') continue;

            $path = $dir . '/' . $item;

            if (is_dir($path)) {
                $this->scanModelsRecursive($path, $prefix . $item . '/');
            } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'php') {
                $modelName = $prefix . basename($item, '.php');
                $this->models[$modelName] = [
                    'file' => $path,
                    'name' => $modelName,
                    'namespace' => $this->extractNamespace($path),
                    'extends' => $this->extractParentClass($path),
                    'db' => 'unknown'
                ];
            }
        }
    }

    /**
     * Rekurzivně prohledá adresář s views
     */
    private function scanViewsDirectory(string $dir, string $prefix = ''): void
    {
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $path = $dir . '/' . $item;
            $relativePath = $prefix . ($prefix ? '/' : '') . $item;

            if (is_dir($path)) {
                $this->scanViewsDirectory($path, $relativePath);
            } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'php') {
                $this->views[$relativePath] = $path;
            }
        }
    }

    /**
     * Extrahuje namespace ze souboru
     */
    private function extractNamespace(string $file): ?string
    {
        $content = file_get_contents($file);
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Extrahuje parent class
     */
    private function extractParentClass(string $file): ?string
    {
        $content = file_get_contents($file);
        if (preg_match('/class\s+\w+\s+extends\s+(\w+)/', $content, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Analyzuje, který model patří do které databáze
     */
    private function analyzeModelsDatabase(): void
    {
        foreach ($this->models as $modelName => &$model) {
            if ($modelName === 'BaseModel') {
                $model['db'] = 'base';
                continue;
            }

            $content = file_get_contents($model['file']);

            // Hledáme definici databázové connection
            // Podle vašeho frameworku - typicky v modelu je:
            // protected $connection = 'db1';
            // nebo
            // public function __construct() { $this->db = 'db2'; }

            if (preg_match('/\$connection\s*=\s*[\'"](db[12])[\'"]/', $content, $matches)) {
                $model['db'] = $matches[1];
            } elseif (preg_match('/\$this->db\s*=\s*[\'"](db[12])[\'"]/', $content, $matches)) {
                $model['db'] = $matches[1];
            } elseif (preg_match('/use\s+db([12])/i', $content, $matches)) {
                $model['db'] = 'db' . $matches[1];
            } else {
                // Zkusíme zjistit podle jmenného prostoru nebo názvu
                if (strpos($modelName, 'Db1') !== false || strpos($modelName, 'DB1') !== false) {
                    $model['db'] = 'db1';
                } elseif (strpos($modelName, 'Db2') !== false || strpos($modelName, 'DB2') !== false) {
                    $model['db'] = 'db2';
                } elseif ($model['extends'] === 'BaseModel') {
                    // Dědí z BaseModel, ale nemá specifikovanou DB - může být problém
                    $model['db'] = 'unknown';
                }
            }

            // Zařadíme do seznamu podle DB
            if ($model['db'] === 'db1') {
                $this->databaseModels['db1'][$modelName] = $model;
            } elseif ($model['db'] === 'db2') {
                $this->databaseModels['db2'][$modelName] = $model;
            }
        }
    }

    /**
     * Seskupí routy podle controlleru
     */
    public function groupRoutesByController(): array
    {
        $grouped = [];

        foreach ($this->routes as $index => $route) {
            [$controllerClass, $method] = $route['action'];

            // Získání názvu controlleru z FQCN
            $parts = explode('\\', $controllerClass);
            $controllerName = end($parts) . '.php';

            if (!isset($grouped[$controllerName])) {
                $grouped[$controllerName] = [
                    'controller' => $controllerName,
                    'class' => $controllerClass,
                    'file' => $this->controllers[$controllerName]['file'] ?? null,
                    'namespace' => $this->controllers[$controllerName]['namespace'] ?? null,
                    'routes' => []
                ];
            }

            $grouped[$controllerName]['routes'][] = [
                'id' => $index,
                'method' => $route['method'],
                'path' => $route['path'],
                'action' => $method,
                'auth' => $route['auth'] ?? false,
                'roles' => $route['roles'] ?? [],
                'menu' => $route['menu'] ?? null,
                'submenu' => $route['submenu'] ?? null,
                'section' => $route['section'] ?? null,
                'title' => $route['title'] ?? null
            ];
        }

        return $grouped;
    }

    /**
     * Analyzuje controller a najde volané modely, services a view
     */
    public function analyzeController(string $controllerFile, array $routes): array
    {
        if (!file_exists($controllerFile)) {
            return [
                'exists' => false,
                'methods' => [],
                'models' => [],
                'services' => [],
                'views' => [],
                'issues' => ["❌ Controller soubor neexistuje: $controllerFile"]
            ];
        }

        $content = file_get_contents($controllerFile);
        $methods = [];
        $usedModels = [];
        $usedServices = [];
        $usedViews = [];
        $issues = [];

        // Zjistíme, zda controller dědí z core/Controller.php
        $extendsCore = false;
        if (preg_match('/extends\s+(\w+)/', $content, $matches)) {
            $parent = $matches[1];
            if ($parent === 'Controller' || strpos($content, 'use App\Core\Controller') !== false) {
                $extendsCore = true;
            }
        }

        if (!$extendsCore) {
            $issues[] = "⚠️ Controller nedědí z core/Controller.php?";
        }

        // Pro každou routu najdeme odpovídající metodu v controlleru
        foreach ($routes as $route) {
            $methodName = $route['action'];
            $methodInfo = $this->analyzeMethod($content, $methodName, $route);

            $methods[$methodName] = $methodInfo;

            // Shromáždíme použité modely, services a view
            foreach ($methodInfo['models'] as $model) {
                $usedModels[$model] = $model;
            }
            foreach ($methodInfo['services'] as $service) {
                $usedServices[$service] = $service;
            }
            foreach ($methodInfo['views'] as $view) {
                $usedViews[$view] = $view;
            }
            $issues = array_merge($issues, $methodInfo['issues']);
        }

        return [
            'exists' => true,
            'extends_core' => $extendsCore,
            'methods' => $methods,
            'models' => array_keys($usedModels),
            'services' => array_keys($usedServices),
            'views' => array_keys($usedViews),
            'issues' => $issues
        ];
    }

    /**
     * Analyzuje konkrétní metodu v controlleru
     */
    private function analyzeMethod(string $controllerContent, string $methodName, array $route): array
    {
        $models = [];
        $services = [];
        $views = [];
        $issues = [];
        $flow = [];

        // Najdeme definici metody
        $pattern = '/function\s+' . preg_quote($methodName) . '\s*\([^)]*\)\s*(?:\{|;)/';
        if (!preg_match($pattern, $controllerContent, $matches, PREG_OFFSET_CAPTURE)) {
            $issues[] = "❌ Metoda $methodName() nenalezena v controlleru";
            return [
                'found' => false,
                'models' => [],
                'services' => [],
                'views' => [],
                'flow' => [],
                'issues' => $issues
            ];
        }

        $startPos = $matches[0][1];

        // Pokud je metoda abstraktní nebo jen deklarace (končí ;), nemá tělo
        if (strpos($matches[0][0], ';') !== false) {
            $issues[] = "⚠️ Metoda $methodName() je pouze deklarace (abstraktní?)";
            return [
                'found' => true,
                'abstract' => true,
                'models' => [],
                'services' => [],
                'views' => [],
                'flow' => ["Metoda $methodName() je abstraktní nebo interface"],
                'issues' => $issues
            ];
        }

        $methodContent = $this->extractMethodContent($controllerContent, $startPos);

        // Flow začátek
        $flow[] = "📌 Route: {$route['method']} {$route['path']} → {$methodName}()";

        // Hledáme volání modelů
        $this->findModelCalls($methodContent, $models, $issues, $flow);

        // Hledáme volání services
        $this->findServiceCalls($methodContent, $services, $issues, $flow);

        // Hledáme renderování view
        $this->findViewCalls($methodContent, $views, $issues, $flow, $route);

        // Hledáme práci s databází (přímo v controlleru - nedoporučuje se)
        if (preg_match('/\$this->db\s*->/', $methodContent)) {
            $issues[] = "⚠️ Přímá práce s databází v controlleru (mělo by být v modelu)";
        }

        return [
            'found' => true,
            'models' => array_unique($models),
            'services' => array_unique($services),
            'views' => array_unique($views),
            'flow' => $flow,
            'issues' => $issues
        ];
    }

    /**
     * Extrahuje obsah metody z kódu controlleru
     */
    private function extractMethodContent(string $content, int $startPos): string
    {
        $length = strlen($content);
        $braceCount = 0;
        $inMethod = false;
        $methodContent = '';

        for ($i = $startPos; $i < $length; $i++) {
            $char = $content[$i];

            if ($char === '{') {
                $braceCount++;
                $inMethod = true;
            } elseif ($char === '}') {
                $braceCount--;
                if ($braceCount === 0 && $inMethod) {
                    $methodContent .= $char;
                    break;
                }
            }

            if ($inMethod) {
                $methodContent .= $char;
            }
        }

        return $methodContent;
    }

    /**
     * Hledá volání modelů v kódu metody
     */
    private function findModelCalls(string $content, array &$models, array &$issues, array &$flow): void
    {
        // Hledání: new Model()
        if (preg_match_all('/new\s+([A-Z][a-zA-Z0-9_]+)\s*\(/', $content, $matches)) {
            foreach ($matches[1] as $model) {
                $modelName = $model . '.php';
                if (isset($this->models[$modelName])) {
                    $models[] = $model;
                    $dbInfo = $this->models[$modelName]['db'] !== 'unknown' ? " (DB: {$this->models[$modelName]['db']})" : "";
                    $flow[] = "  ➜ new $model(){$dbInfo}";
                } elseif ($model !== 'self' && $model !== 'static' && !isset($this->services[$modelName])) {
                    $issues[] = "❌ Model $model.php nenalezen (ale je volán jako new $model())";
                }
            }
        }

        // Hledání: Model::method() - statické volání
        if (preg_match_all('/([A-Z][a-zA-Z0-9_]+)::(?!class)/', $content, $matches)) {
            foreach ($matches[1] as $model) {
                $modelName = $model . '.php';
                if (isset($this->models[$modelName])) {
                    $models[] = $model;
                    $dbInfo = $this->models[$modelName]['db'] !== 'unknown' ? " (DB: {$this->models[$modelName]['db']})" : "";
                    $flow[] = "  ➜ $model::method(){$dbInfo} (statické volání)";
                } elseif (!isset($this->services[$modelName]) && !in_array($model, ['self', 'static', 'parent'])) {
                    $issues[] = "❌ Třída $model volaná staticky, ale není model ani service?";
                }
            }
        }

        // Hledání: $this->model (pokud je injektovaný)
        if (preg_match_all('/\$this->([a-zA-Z0-9_]+)(?:->|\s*=\s*)/', $content, $matches)) {
            foreach ($matches[1] as $prop) {
                // Toto je složitější - mohlo by to být v __construct
                $issues[] = "ℹ️ Detekováno volání \$this->$prop (pravděpodobně injektovaný model/service) - zkontrolujte konstruktor";
            }
        }
    }

    /**
     * Hledá volání services v kódu metody
     */
    private function findServiceCalls(string $content, array &$services, array &$issues, array &$flow): void
    {
        // Hledání: new Service()
        if (preg_match_all('/new\s+([A-Z][a-zA-Z0-9_]+Service)\s*\(/', $content, $matches)) {
            foreach ($matches[1] as $service) {
                $serviceFile = $service . '.php';
                if (isset($this->services[$serviceFile])) {
                    $services[] = $service;
                    $flow[] = "  ➜ new $service() (service)";
                } else {
                    $issues[] = "❌ Service $service.php nenalezena (ale je volána)";
                }
            }
        }

        // Hledání: Service::method() (statické volání)
        if (preg_match_all('/([A-Z][a-zA-Z0-9_]+Service)::/', $content, $matches)) {
            foreach ($matches[1] as $service) {
                $serviceFile = $service . '.php';
                if (isset($this->services[$serviceFile])) {
                    $services[] = $service;
                    $flow[] = "  ➜ $service::method() (statické volání service)";
                } else {
                    $issues[] = "❌ Service $service.php nenalezena (statické volání)";
                }
            }
        }
    }

    /**
     * Hledá renderování view v kódu metody
     */
    private function findViewCalls(string $content, array &$views, array &$issues, array &$flow, array $route): void
    {
        // Hledání: require 'cesta/view.php'
        if (preg_match_all('/require\s+[\'"]([^\'"]+\.php)[\'"]/', $content, $matches)) {
            foreach ($matches[1] as $view) {
                $viewPath = ltrim($view, '/');
                if (isset($this->views[$viewPath])) {
                    $views[] = $viewPath;
                    $flow[] = "  ➜ require '$viewPath'";
                } else {
                    $issues[] = "❌ View $viewPath nenalezeno (voláno require)";
                }
            }
        }

        // Hledání: include 'cesta/view.php'
        if (preg_match_all('/include\s+[\'"]([^\'"]+\.php)[\'"]/', $content, $matches)) {
            foreach ($matches[1] as $view) {
                $viewPath = ltrim($view, '/');
                if (isset($this->views[$viewPath])) {
                    $views[] = $viewPath;
                    $flow[] = "  ➜ include '$viewPath'";
                } else {
                    $issues[] = "❌ View $viewPath nenalezeno (voláno include)";
                }
            }
        }

        // Hledání: $this->view->render('view', ...) - běžné v MVC
        if (preg_match_all('/\$this->view\s*->\s*render\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
            foreach ($matches[1] as $view) {
                $viewFile = $view . '.php';
                $viewPath = str_replace('.', '/', $viewFile);

                $found = false;
                foreach ($this->views as $path => $fullPath) {
                    if (strpos($path, $viewFile) !== false || strpos($path, $viewPath) !== false) {
                        $views[] = $path;
                        $flow[] = "  ➜ render view: '$path'";
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    // Zkusíme hledat bez .php
                    foreach ($this->views as $path => $fullPath) {
                        if (strpos($path, $view) !== false) {
                            $views[] = $path;
                            $flow[] = "  ➜ render view: '$path'";
                            $found = true;
                            break;
                        }
                    }
                }

                if (!$found) {
                    $issues[] = "❌ View pro '$view' nenalezeno (render)";
                }
            }
        }

        // Pokud metoda nemá žádné view, ale je to GET route, může to být problém
        if (empty($views) && $route['method'] === 'GET' && !isset($route['api'])) {
            // Přeskočíme routy, které jsou API nebo redirect
            if (strpos($content, 'redirect') === false &&
                strpos($content, 'header') === false &&
                strpos($content, 'json') === false &&
                strpos($content, 'Response') === false) {
                $issues[] = "⚠️ GET metoda {$route['action']}() nerenderuje žádné view (možná API nebo redirect?)";
            } else {
                $flow[] = "  ➜ [API nebo redirect - nerenderuje view]";
            }
        }
    }

    /**
     * Kompletní analýza všech controllerů
     */
    public function analyzeAll(): array
    {
        $groupedRoutes = $this->groupRoutesByController();
        $report = [];

        foreach ($groupedRoutes as $controllerName => $data) {
            echo "🔍 Analyzuji $controllerName...\n";

            $controllerAnalysis = $this->analyzeController(
                $data['file'] ?? '',
                $data['routes']
            );

            // Přidáme informace o DB modelech použitých v tomto controlleru
            $modelsWithDb = [];
            foreach ($controllerAnalysis['models'] as $model) {
                $modelKey = $model . '.php';
                if (isset($this->models[$modelKey])) {
                    $modelsWithDb[$model] = $this->models[$modelKey]['db'];
                } else {
                    $modelsWithDb[$model] = 'unknown';
                }
            }

            $report[$controllerName] = [
                'controller' => $data['controller'],
                'class' => $data['class'],
                'namespace' => $data['namespace'],
                'file' => $data['file'],
                'file_exists' => $controllerAnalysis['exists'],
                'extends_core' => $controllerAnalysis['extends_core'] ?? false,
                'route_count' => count($data['routes']),
                'routes' => $data['routes'],
                'methods' => $controllerAnalysis['methods'],
                'models' => $modelsWithDb,
                'services' => $controllerAnalysis['services'],
                'views' => $controllerAnalysis['views'],
                'issues' => $controllerAnalysis['issues']
            ];
        }

        return $report;
    }

    /**
     * Vygeneruje HTML report
     */
    public function generateHtmlReport(array $report): string
    {
        $html = '<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Flow Analyzer Report</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f7fb;
            padding: 2rem;
        }
        .container {
            max-width: 1600px;
            margin: 0 auto;
        }
        h1 {
            margin-bottom: 1.5rem;
            color: #1e293b;
            font-weight: 600;
        }
        .summary {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        .stat {
            text-align: center;
        }
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2563eb;
            line-height: 1.2;
        }
        .stat-label {
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }
        .db-stats {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1rem;
        }
        .db-badge {
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .db-badge.db1 { background: #dbeafe; color: #1e40af; }
        .db-badge.db2 { background: #fae8ff; color: #6b21a8; }
        .controller {
            background: white;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            overflow: hidden;
        }
        .controller-header {
            background: #1e293b;
            color: white;
            padding: 1rem 1.5rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .controller-header:hover {
            background: #334155;
        }
        .controller-title {
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            background: #475569;
            color: white;
        }
        .badge-success { background: #10b981; }
        .badge-warning { background: #f59e0b; }
        .badge-danger { background: #ef4444; }
        .badge-info { background: #3b82f6; }
        .controller-content {
            padding: 1.5rem;
            display: none;
        }
        .controller-header.active + .controller-content {
            display: block;
        }
        .routes-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        .routes-table th {
            text-align: left;
            padding: 0.75rem;
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
        }
        .routes-table td {
            padding: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .method-get {
            background: #e6f7e6;
            color: #0a5e0a;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.75rem;
            display: inline-block;
        }
        .method-post {
            background: #fff3e0;
            color: #b45b0a;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.75rem;
            display: inline-block;
        }
        .flow {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            font-family: monospace;
            white-space: pre-wrap;
            border-left: 4px solid #2563eb;
        }
        .issues {
            background: #fef2f2;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            border-left: 4px solid #ef4444;
        }
        .issue-item {
            color: #991b1b;
            margin-bottom: 0.25rem;
        }
        .dependencies {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin: 0.5rem 0;
        }
        .dep-tag {
            padding: 0.25rem 0.75rem;
            background: #e2e8f0;
            border-radius: 9999px;
            font-size: 0.875rem;
            color: #334155;
        }
        .dep-tag.model { background: #dbeafe; color: #1e40af; }
        .dep-tag.model-db1 { background: #dbeafe; color: #1e40af; border-left: 4px solid #2563eb; }
        .dep-tag.model-db2 { background: #fae8ff; color: #6b21a8; border-left: 4px solid #9333ea; }
        .dep-tag.service { background: #fae8ff; color: #6b21a8; }
        .dep-tag.view { background: #dcfce7; color: #166534; }
        .missing {
            background: #fee2e2;
            color: #991b1b;
            text-decoration: line-through;
            opacity: 0.7;
        }
        .toggle-all {
            margin-bottom: 1rem;
            padding: 0.5rem 1rem;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        .toggle-all:hover {
            background: #1d4ed8;
        }
        .filter-input {
            padding: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            width: 300px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Route Flow Analyzer Report</h1>';

        // Statistiky
        $totalRoutes = 0;
        $totalControllers = count($report);
        $totalIssues = 0;
        $totalModels = 0;
        $totalServices = 0;
        $totalViews = 0;
        $modelsDb1 = count($this->databaseModels['db1']);
        $modelsDb2 = count($this->databaseModels['db2']);

        foreach ($report as $data) {
            $totalRoutes += $data['route_count'];
            $totalIssues += count($data['issues']);
            $totalModels += count($data['models']);
            $totalServices += count($data['services']);
            $totalViews += count($data['views']);
        }

        $html .= '
        <div class="summary">
            <div class="stat">
                <div class="stat-value">' . $totalControllers . '</div>
                <div class="stat-label">Controllerů</div>
            </div>
            <div class="stat">
                <div class="stat-value">' . $totalRoutes . '</div>
                <div class="stat-label">Rout</div>
            </div>
            <div class="stat">
                <div class="stat-value">' . $totalModels . '</div>
                <div class="stat-label">Použitých modelů</div>
            </div>
            <div class="stat">
                <div class="stat-value">' . $totalServices . '</div>
                <div class="stat-label">Použitých servis</div>
            </div>
            <div class="stat">
                <div class="stat-value">' . $totalViews . '</div>
                <div class="stat-label">Použitých view</div>
            </div>
            <div class="stat">
                <div class="stat-value ' . ($totalIssues > 0 ? 'badge-danger' : 'badge-success') . '" style="color: ' . ($totalIssues > 0 ? '#ef4444' : '#10b981') . '">' . $totalIssues . '</div>
                <div class="stat-label">Nalezených problémů</div>
            </div>
        </div>

        <div class="db-stats">
            <div class="db-badge db1">📦 DB1: ' . $modelsDb1 . ' modelů</div>
            <div class="db-badge db2">📦 DB2: ' . $modelsDb2 . ' modelů</div>
        </div>

        <div style="margin: 1rem 0;">
            <input type="text" id="filterInput" class="filter-input" placeholder="Filtrovat controllery...">
            <button class="toggle-all" onclick="toggleAll()">Rozbalit vše</button>
            <button class="toggle-all" onclick="expandWithIssues()">Rozbalit s problémy</button>
        </div>';

        // Jednotlivé controllery
        foreach ($report as $controllerName => $data) {
            $hasIssues = !empty($data['issues']);
            $missingFile = !$data['file_exists'];
            $controllerId = 'ctrl_' . preg_replace('/[^a-zA-Z0-9]/', '_', $controllerName);

            $html .= '
        <div class="controller" data-controller="' . htmlspecialchars(strtolower($controllerName)) . '">
            <div class="controller-header" onclick="toggleController(this)">
                <div class="controller-title">
                    <span>' . htmlspecialchars($controllerName) . '</span>';

            if ($data['namespace']) {
                $html .= '<span class="badge badge-info">' . htmlspecialchars($data['namespace']) . '</span>';
            }

            $html .= '<span class="badge">' . count($data['routes']) . ' rout</span>';

            if (!$data['extends_core']) {
                $html .= '<span class="badge badge-warning">Nedědí z Core</span>';
            }

            if ($missingFile) {
                $html .= '<span class="badge badge-danger">CHYBÍ SOUBOR</span>';
            } elseif ($hasIssues) {
                $html .= '<span class="badge badge-warning">' . count($data['issues']) . ' problémů</span>';
            } else {
                $html .= '<span class="badge badge-success">OK</span>';
            }

            $html .= '
                </div>
                <div>▼</div>
            </div>
            <div class="controller-content">';

            if ($missingFile) {
                $html .= '<div class="issues"><div class="issue-item">❌ Controller soubor neexistuje: ' . htmlspecialchars($data['file']) . '</div></div>';
            } else {
                // Tabulka rout
                $html .= '<h3>📋 Routy (z app/Config/routes.php)</h3>
                <table class="routes-table">
                    <thead>
                        <tr>
                            <th>Metoda</th>
                            <th>Path</th>
                            <th>Akce</th>
                            <th>Auth</th>
                            <th>Role</th>
                            <th>Menu/Title</th>
                        </tr>
                    </thead>
                    <tbody>';

                foreach ($data['routes'] as $route) {
                    $methodClass = $route['method'] === 'GET' ? 'method-get' : 'method-post';
                    $authBadge = $route['auth'] ? '🔒' : '🔓';
                    $roles = implode(', ', $route['roles'] ?? ['žádné']);
                    $menuInfo = ($route['menu'] ?? '') . ($route['submenu'] ? ' > ' . $route['submenu'] : '');
                    if (empty($menuInfo)) $menuInfo = $route['title'] ?? '-';

                    $html .= '<tr>
                        <td><span class="' . $methodClass . '">' . $route['method'] . '</span></td>
                        <td><code>' . htmlspecialchars($route['path']) . '</code></td>
                        <td><code>' . htmlspecialchars($route['action']) . '()</code></td>
                        <td>' . $authBadge . '</td>
                        <td>' . htmlspecialchars($roles) . '</td>
                        <td>' . htmlspecialchars($menuInfo) . '</td>
                    </tr>';
                }

                $html .= '
                    </tbody>
                </table>';

                // Flow pro každou metodu
                $html .= '<h3>🔄 Flow</h3>';
                foreach ($data['methods'] as $methodName => $methodData) {
                    if (!$methodData['found']) {
                        $html .= '<div class="issues"><div class="issue-item">❌ Metoda ' . htmlspecialchars($methodName) . '() nenalezena v controlleru</div></div>';
                        continue;
                    }

                    $html .= '<h4>' . htmlspecialchars($methodName) . '()</h4>';

                    // Flow
                    if (!empty($methodData['flow'])) {
                        $html .= '<div class="flow">';
                        foreach ($methodData['flow'] as $line) {
                            $html .= htmlspecialchars($line) . "\n";
                        }
                        $html .= '</div>';
                    }

                    // Dependencies
                    $allDeps = [];
                    foreach ($methodData['models'] as $model) {
                        $db = isset($data['models'][$model]) ? $data['models'][$model] : 'unknown';
                        $allDeps[] = ['type' => 'model', 'name' => $model, 'db' => $db];
                    }
                    foreach ($methodData['services'] as $service) {
                        $allDeps[] = ['type' => 'service', 'name' => $service, 'db' => null];
                    }
                    foreach ($methodData['views'] as $view) {
                        $allDeps[] = ['type' => 'view', 'name' => $view, 'db' => null];
                    }

                    if (!empty($allDeps)) {
                        $html .= '<div class="dependencies">';
                        foreach ($allDeps as $dep) {
                            $exists = true;
                            if ($dep['type'] === 'model') {
                                $exists = isset($this->models[$dep['name'] . '.php']);
                            }
                            if ($dep['type'] === 'service') {
                                $exists = isset($this->services[$dep['name'] . '.php']);
                            }
                            if ($dep['type'] === 'view') {
                                $exists = isset($this->views[$dep['name']]);
                            }

                            $class = 'dep-tag ' . $dep['type'];
                            if ($dep['type'] === 'model' && $dep['db'] === 'db1') $class .= ' model-db1';
                            if ($dep['type'] === 'model' && $dep['db'] === 'db2') $class .= ' model-db2';
                            if (!$exists) $class .= ' missing';

                            $html .= '<span class="' . $class . '">';
                            if ($dep['type'] === 'model') $html .= '📦';
                            elseif ($dep['type'] === 'service') $html .= '⚙️';
                            else $html .= '🎨';

                            $html .= ' ' . htmlspecialchars($dep['name']);
                            if ($dep['type'] === 'model' && $dep['db'] !== 'unknown') {
                                $html .= ' (' . strtoupper($dep['db']) . ')';
                            }
                            if (!$exists) $html .= ' (chybí)';
                            $html .= '</span>';
                        }
                        $html .= '</div>';
                    }
                }

                // Issues
                if (!empty($data['issues'])) {
                    $html .= '<div class="issues"><h4>⚠️ Nalezené problémy</h4>';
                    foreach ($data['issues'] as $issue) {
                        $html .= '<div class="issue-item">• ' . htmlspecialchars($issue) . '</div>';
                    }
                    $html .= '</div>';
                }

                // Přehled použitých modelů podle DB
                $modelsByDb = ['db1' => [], 'db2' => [], 'unknown' => []];
                foreach ($data['models'] as $model => $db) {
                    $modelsByDb[$db][] = $model;
                }

                if (!empty($modelsByDb['db1']) || !empty($modelsByDb['db2'])) {
                    $html .= '<h4>📊 Databázové modely</h4>';
                    if (!empty($modelsByDb['db1'])) {
                        $html .= '<div><strong>DB1:</strong> ' . implode(', ', array_map('htmlspecialchars', $modelsByDb['db1'])) . '</div>';
                    }
                    if (!empty($modelsByDb['db2'])) {
                        $html .= '<div><strong>DB2:</strong> ' . implode(', ', array_map('htmlspecialchars', $modelsByDb['db2'])) . '</div>';
                    }
                }
            }

            $html .= '
            </div>
        </div>';
        }

        $html .= '
    </div>

    <script>
        function toggleController(header) {
            header.classList.toggle("active");
        }

        function toggleAll() {
            const headers = document.querySelectorAll(".controller-header");
            headers.forEach(header => {
                header.classList.add("active");
            });
        }

        function expandWithIssues() {
            const headers = document.querySelectorAll(".controller-header");
            headers.forEach(header => {
                const content = header.nextElementSibling;
                if (content && content.querySelector(".issues")) {
                    header.classList.add("active");
                }
            });
        }

        // Filtrování
        document.getElementById("filterInput").addEventListener("keyup", function() {
            const filter = this.value.toLowerCase();
            const controllers = document.querySelectorAll(".controller");

            controllers.forEach(controller => {
                const name = controller.getAttribute("data-controller");
                if (name.includes(filter)) {
                    controller.style.display = "block";
                } else {
                    controller.style.display = "none";
                }
            });
        });
    </script>
</body>
</html>';

        return $html;
    }

    /**
     * Vygeneruje Markdown report
     */
    public function generateMarkdownReport(array $report): string
    {
        $md = "# Route Flow Analyzer Report\n\n";
        $md .= "Generováno: " . date('Y-m-d H:i:s') . "\n\n";

        // Statistiky
        $totalRoutes = 0;
        $totalControllers = count($report);
        $totalIssues = 0;
        $totalModels = 0;
        $totalServices = 0;
        $totalViews = 0;
        $modelsDb1 = count($this->databaseModels['db1']);
        $modelsDb2 = count($this->databaseModels['db2']);

        foreach ($report as $data) {
            $totalRoutes += $data['route_count'];
            $totalIssues += count($data['issues']);
            $totalModels += count($data['models']);
            $totalServices += count($data['services']);
            $totalViews += count($data['views']);
        }

        $md .= "## Souhrn\n\n";
        $md .= "- **Controllerů:** $totalControllers\n";
        $md .= "- **Rout:** $totalRoutes\n";
        $md .= "- **Použitých modelů:** $totalModels\n";
        $md .= "- **Použitých servis:** $totalServices\n";
        $md .= "- **Použitých view:** $totalViews\n";
        $md .= "- **Nalezených problémů:** $totalIssues\n\n";

        $md .= "### Databáze\n\n";
        $md .= "- **DB1:** $modelsDb1 modelů\n";
        $md .= "- **DB2:** $modelsDb2 modelů\n\n";

        // Jednotlivé controllery
        foreach ($report as $controllerName => $data) {
            $md .= "## " . $controllerName . "\n\n";
            $md .= "- **Třída:** " . $data['class'] . "\n";
            if ($data['namespace']) {
                $md .= "- **Namespace:** " . $data['namespace'] . "\n";
            }
            $md .= "- **Soubor:** " . ($data['file'] ?? 'Nenalezen') . "\n";
            $md .= "- **Počet rout:** " . count($data['routes']) . "\n";
            $md .= "- **Dědí z Core:** " . ($data['extends_core'] ? 'Ano' : 'Ne') . "\n\n";

            if (!$data['file_exists']) {
                $md .= "❌ **CHYBA:** Controller soubor neexistuje!\n\n";
                continue;
            }

            // Tabulka rout
            $md .= "### Routy\n\n";
            $md .= "| Metoda | Path | Akce | Auth | Role | Menu/Title |\n";
            $md .= "|--------|------|------|------|------|------------|\n";

            foreach ($data['routes'] as $route) {
                $auth = $route['auth'] ? '🔒' : '🔓';
                $roles = implode(', ', $route['roles'] ?? ['-']);
                $menu = $route['menu'] ?? '';
                if ($route['submenu']) $menu .= ' > ' . $route['submenu'];
                if (!$menu) $menu = $route['title'] ?? '-';

                $md .= "| {$route['method']} | `{$route['path']}` | `{$route['action']}()` | $auth | $roles | $menu |\n";
            }

            $md .= "\n";

            // Flow pro každou metodu
            $md .= "### Flow\n\n";

            foreach ($data['methods'] as $methodName => $methodData) {
                $md .= "#### `{$methodName}()`\n\n";

                if (!$methodData['found']) {
                    $md .= "❌ **Metoda nenalezena!**\n\n";
                    continue;
                }

                if (!empty($methodData['flow'])) {
                    $md .= "```\n";
                    foreach ($methodData['flow'] as $line) {
                        $md .= $line . "\n";
                    }
                    $md .= "```\n\n";
                }

                // Závislosti
                if (!empty($methodData['models']) || !empty($methodData['services']) || !empty($methodData['views'])) {
                    $md .= "**Použité závislosti:**\n\n";

                    foreach ($methodData['models'] as $model) {
                        $exists = isset($this->models[$model . '.php']) ? '✅' : '❌';
                        $db = isset($data['models'][$model]) ? $data['models'][$model] : 'unknown';
                        $dbTag = $db !== 'unknown' ? " (DB: " . strtoupper($db) . ")" : "";
                        $md .= "- $exists Model: `$model`$dbTag\n";
                    }

                    foreach ($methodData['services'] as $service) {
                        $exists = isset($this->services[$service . '.php']) ? '✅' : '❌';
                        $md .= "- $exists Service: `$service`\n";
                    }

                    foreach ($methodData['views'] as $view) {
                        $exists = isset($this->views[$view]) ? '✅' : '❌';
                        $md .= "- $exists View: `$view`\n";
                    }

                    $md .= "\n";
                }
            }

            // Problémy
            if (!empty($data['issues'])) {
                $md .= "### Nalezené problémy\n\n";
                foreach ($data['issues'] as $issue) {
                    $md .= "- ⚠️ $issue\n";
                }
                $md .= "\n";
            }

            // Přehled modelů podle DB
            $modelsByDb = ['db1' => [], 'db2' => [], 'unknown' => []];
            foreach ($data['models'] as $model => $db) {
                $modelsByDb[$db][] = $model;
            }

            if (!empty($modelsByDb['db1']) || !empty($modelsByDb['db2'])) {
                $md .= "### Použité databáze\n\n";
                if (!empty($modelsByDb['db1'])) {
                    $md .= "- **DB1:** " . implode(', ', $modelsByDb['db1']) . "\n";
                }
                if (!empty($modelsByDb['db2'])) {
                    $md .= "- **DB2:** " . implode(', ', $modelsByDb['db2']) . "\n";
                }
                $md .= "\n";
            }

            $md .= "---\n\n";
        }

        return $md;
    }

    /**
     * Spuštění analýzy a generování reportů
     */
    public function run(string $format = 'both', ?string $controllerFilter = null): void
    {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "   🔍 ROUTE FLOW ANALYZER pro vlastní MVC framework\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";

        echo "📁 Projekt: " . $this->projectRoot . "\n";
        echo "📁 Konfigurace: app/Config/routes.php\n";
        echo "📁 Controllery: app/Controllers/\n";
        echo "📁 Modely: app/Models/ (včetně BaseModel)\n";
        echo "📁 Services: app/Services/\n";
        echo "📁 Views: app/Views/\n";
        echo "⚙️ Core: app/Core/ (Controller.php, Config.php)\n\n";

        echo "🔍 Spouštím analýzu...\n";

        $report = $this->analyzeAll();

        if ($controllerFilter) {
            $filtered = [];
            foreach ($report as $name => $data) {
                if (stripos($name, $controllerFilter) !== false) {
                    $filtered[$name] = $data;
                }
            }
            $report = $filtered;
            echo "🎯 Filtrováno podle: $controllerFilter\n";
        }

        // Shrnutí problémů
        $totalIssues = 0;
        $controllersWithIssues = 0;
        foreach ($report as $data) {
            if (!empty($data['issues'])) {
                $controllersWithIssues++;
                $totalIssues += count($data['issues']);
            }
        }

        echo "\n📊 Výsledky analýzy:\n";
        echo "   - Zpracováno " . count($report) . " controllerů\n";
        echo "   - " . $controllersWithIssues . " controllerů má problémy\n";
        echo "   - Celkem $totalIssues problémů\n\n";

        if ($format === 'html' || $format === 'both') {
            echo "📄 Generuji HTML report...\n";
            $html = $this->generateHtmlReport($report);
            $htmlFile = $this->projectRoot . '/route-flow-report.html';
            file_put_contents($htmlFile, $html);
            echo "   ✅ HTML report uložen: " . $htmlFile . "\n";
        }

        if ($format === 'md' || $format === 'both') {
            echo "📄 Generuji Markdown report...\n";
            $md = $this->generateMarkdownReport($report);
            $mdFile = $this->projectRoot . '/route-flow-report.md';
            file_put_contents($mdFile, $md);
            echo "   ✅ Markdown report uložen: " . $mdFile . "\n";
        }

        echo "\n═══════════════════════════════════════════════════════════════\n";
        echo "   🎉 Analýza dokončena!\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";
    }
}

// Spuštění
$options = getopt('', ['format::', 'controller::']);
$format = $options['format'] ?? 'both';
$controller = $options['controller'] ?? null;

$analyzer = new RouteFlowAnalyzer(__DIR__);
$analyzer->run($format, $controller);