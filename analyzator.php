<?php
/**
 * KOMPLETNÍ ROUTE FLOW ANALYZER S MAPOU PROJEKTU
 * ==============================================
 *
 * FÁZE 1: Vytvoří kompletní mapu projektu (všechny soubory, třídy, metody)
 * FÁZE 2: Pro každou routu simuluje flow od index.php po view
 * FÁZE 3: Interaktivní HTML report s klikacími routami
 *
 * Použití: php analyzator.php
 */

class ProjectMapper
{
    private string $rootPath;
    private array $projectMap = [
        'structure' => [],
        'classes' => [],
        'core' => [],
        'models' => [],
        'controllers' => [],
        'views' => [],
        'routes' => [],
        'issues' => []
    ];

    private array $excludeDirs = ['.git', '.idea', 'node_modules', 'vendor', 'storage', 'cache'];
    private array $includeExts = ['php', 'html', 'css', 'js', 'json', 'xml'];

    public function __construct(string $rootPath)
    {
        $this->rootPath = rtrim($rootPath, '/\\');
    }

    /**
     * FÁZE 1: Vytvoření kompletní mapy projektu
     */
    public function createMap(): array
    {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "   📁 FÁZE 1: Vytvářím kompletní mapu projektu\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";

        // 1. Projde celý adresář
        $this->scanDirectory($this->rootPath, '');

        // 2. Analyzuje PHP soubory (třídy, metody, namespace)
        $this->analyzePhpFiles();

        // 3. Načte routy
        $this->loadRoutes();

        // 4. Statistiky
        $this->printStats();

        return $this->projectMap;
    }

    /**
     * Rekurzivně projde adresář
     */
    private function scanDirectory(string $dir, string $relativePath): void
    {
        $items = scandir($dir);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            if (in_array($item, $this->excludeDirs)) continue;

            $fullPath = $dir . DIRECTORY_SEPARATOR . $item;
            $relPath = $relativePath ? $relativePath . '/' . $item : $item;

            if (is_dir($fullPath)) {
                // Přidáme do struktury
                $this->projectMap['structure'][$relPath] = [
                    'type' => 'dir',
                    'path' => $fullPath,
                    'children' => []
                ];

                $this->scanDirectory($fullPath, $relPath);
            } else {
                // Přidáme soubor
                $ext = pathinfo($item, PATHINFO_EXTENSION);
                $this->projectMap['structure'][$relPath] = [
                    'type' => 'file',
                    'path' => $fullPath,
                    'ext' => $ext,
                    'size' => filesize($fullPath),
                    'modified' => filemtime($fullPath)
                ];

                // Speciální složky
                if (str_contains($relPath, 'app/Core/')) {
                    $this->projectMap['core'][basename($item, '.php')] = $fullPath;
                }
                elseif (str_contains($relPath, 'app/Controllers/')) {
                    $this->projectMap['controllers'][basename($item, '.php')] = $fullPath;
                }
                elseif (str_contains($relPath, 'app/Models/')) {
                    $this->projectMap['models'][basename($item, '.php')] = $fullPath;
                }
                elseif (str_contains($relPath, 'app/Views/') && $ext === 'php') {
                    $viewName = str_replace(['app/Views/', '.php'], '', $relPath);
                    $this->projectMap['views'][$viewName] = $fullPath;
                }
            }
        }
    }

    /**
     * Analyzuje PHP soubory (třídy, metody, namespace)
     */
    private function analyzePhpFiles(): void
    {
        echo "\n🔍 Analyzuji PHP soubory...\n";

        $phpFiles = array_filter($this->projectMap['structure'], function($file) {
            return $file['type'] === 'file' && $file['ext'] === 'php';
        });

        $total = count($phpFiles);
        $current = 0;

        foreach ($phpFiles as $relPath => $info) {
            $current++;
            if ($current % 10 === 0) {
                echo "\r   Zpracováno $current/$total PHP souborů...";
            }

            $content = file_get_contents($info['path']);

            // Extrahujeme namespace
            $namespace = null;
            if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
                $namespace = trim($matches[1]);
            }

            // Extrahujeme třídy
            if (preg_match('/class\s+(\w+)(?:\s+extends\s+(\w+))?/', $content, $matches)) {
                $className = $matches[1];
                $extends = $matches[2] ?? null;

                $fullClassName = $namespace ? $namespace . '\\' . $className : $className;

                // Extrahujeme metody
                $methods = [];
                if (preg_match_all('/(public|protected|private)?\s*function\s+(\w+)\s*\(([^)]*)\)(?:\s*:\s*([^{;]+))?/', $content, $methodMatches, PREG_SET_ORDER)) {
                    foreach ($methodMatches as $method) {
                        $visibility = $method[1] ?? 'public';
                        $methodName = $method[2];
                        $params = trim($method[3]);
                        $returnType = trim($method[4] ?? '');

                        $methods[$methodName] = [
                            'visibility' => $visibility,
                            'params' => $params,
                            'return_type' => $returnType,
                            'start_line' => $this->findMethodLine($content, $methodName)
                        ];
                    }
                }

                $this->projectMap['classes'][$fullClassName] = [
                    'file' => $info['path'],
                    'namespace' => $namespace,
                    'name' => $className,
                    'extends' => $extends,
                    'methods' => $methods,
                    'uses' => $this->extractUseStatements($content)
                ];
            }
        }

        echo "\r   ✅ Zpracováno $total PHP souborů\n";
    }

    /**
     * Extrahuje use statements
     */
    private function extractUseStatements(string $content): array
    {
        $uses = [];
        if (preg_match_all('/^use\s+([^;]+);/m', $content, $matches)) {
            foreach ($matches[1] as $use) {
                $uses[] = trim($use);
            }
        }
        return $uses;
    }

    /**
     * Najde řádek, kde začíná metoda
     */
    private function findMethodLine(string $content, string $methodName): int
    {
        $lines = explode("\n", $content);
        foreach ($lines as $i => $line) {
            if (str_contains($line, "function $methodName(")) {
                return $i + 1;
            }
        }
        return 0;
    }

    /**
     * Načte routy
     */
    private function loadRoutes(): void
    {
        echo "\n📋 Načítám routy...\n";

        $routesFile = $this->rootPath . '/app/Config/routes.php';

        if (!file_exists($routesFile)) {
            $this->projectMap['issues'][] = "❌ routes.php nenalezen v $routesFile";
            return;
        }

        $routes = require $routesFile;

        if (!is_array($routes)) {
            $this->projectMap['issues'][] = "❌ routes.php nevrátil pole";
            return;
        }

        foreach ($routes as $index => $route) {
            if (!isset($route['method'], $route['path'], $route['action'])) {
                $this->projectMap['issues'][] = "⚠️ Chybná routa #$index: chybí method/path/action";
                continue;
            }

            if (!is_array($route['action']) || count($route['action']) < 2) {
                $this->projectMap['issues'][] = "⚠️ Chybná routa #$index: action musí být [Controller::class, 'method']";
                continue;
            }

            $this->projectMap['routes'][] = [
                'id' => $index,
                'method' => $route['method'],
                'path' => $route['path'],
                'controller' => $route['action'][0],
                'action' => $route['action'][1],
                'auth' => $route['auth'] ?? false,
                'roles' => $route['roles'] ?? [],
                'menu' => $route['menu'] ?? null,
                'title' => $route['title'] ?? null
            ];
        }

        echo "   ✅ Načteno " . count($this->projectMap['routes']) . " rout\n";
    }

    /**
     * Výpis statistik
     */
    private function printStats(): void
    {
        echo "\n📊 Statistika projektu:\n";
        echo "   - Celkem souborů: " . count($this->projectMap['structure']) . "\n";
        echo "   - PHP souborů: " . count(array_filter($this->projectMap['structure'], fn($f) => $f['type'] === 'file' && $f['ext'] === 'php')) . "\n";
        echo "   - Tříd: " . count($this->projectMap['classes']) . "\n";
        echo "   - Controllerů: " . count($this->projectMap['controllers']) . "\n";
        echo "   - Modelů: " . count($this->projectMap['models']) . "\n";
        echo "   - View: " . count($this->projectMap['views']) . "\n";
        echo "   - Core souborů: " . count($this->projectMap['core']) . "\n";
        echo "   - Rout: " . count($this->projectMap['routes']) . "\n";

        if (!empty($this->projectMap['issues'])) {
            echo "\n⚠️ Nalezené problémy v mapě:\n";
            foreach ($this->projectMap['issues'] as $issue) {
                echo "   - $issue\n";
            }
        }
    }
}

class RouteFlowAnalyzer
{
    private array $projectMap;
    private array $flowReport = [];
    private string $rootPath;

    public function __construct(array $projectMap, string $rootPath)
    {
        $this->projectMap = $projectMap;
        $this->rootPath = $rootPath;
    }

    /**
     * FÁZE 2: Analýza flow pro každou routu
     */
    public function analyzeAllRoutes(): array
    {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "   🔍 FÁZE 2: Analyzuji flow pro každou routu\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";

        $total = count($this->projectMap['routes']);
        $current = 0;

        foreach ($this->projectMap['routes'] as $route) {
            $current++;
            echo "\r   Zpracovávám routu $current/$total...";

            $flow = $this->analyzeRouteFlow($route);
            $this->flowReport[] = $flow;
        }

        echo "\n";
        return $this->flowReport;
    }

    /**
     * Analyzuje flow jedné routy
     */
    private function analyzeRouteFlow(array $route): array
    {
        $flow = [
            'route' => $route,
            'steps' => [],
            'issues' => [],
            'warnings' => [],
            'success' => true
        ];

        // Krok 1: Bootstrap
        $flow['steps'][] = $this->analyzeBootstrap();

        // Krok 2: Controller
        $controllerStep = $this->analyzeController($route['controller']);
        $flow['steps'][] = $controllerStep;

        if (!$controllerStep['exists']) {
            $flow['issues'][] = "❌ Controller {$route['controller']} neexistuje";
            $flow['success'] = false;
            return $flow;
        }

        // Krok 3: Metoda v controlleru
        $methodStep = $this->analyzeMethod($controllerStep, $route['action']);
        $flow['steps'][] = $methodStep;

        if (!$methodStep['exists']) {
            $flow['issues'][] = "❌ Metoda {$route['action']} neexistuje v " . $route['controller'];
            $flow['success'] = false;
            return $flow;
        }

        // Krok 4: Volání modelů
        $modelsStep = $this->analyzeModelCalls($methodStep);
        $flow['steps'][] = $modelsStep;
        $flow['issues'] = array_merge($flow['issues'], $modelsStep['issues']);

        // Krok 5: Flash zprávy
        $flashStep = $this->analyzeFlashCalls($methodStep);
        $flow['steps'][] = $flashStep;
        $flow['issues'] = array_merge($flow['issues'], $flashStep['issues']);

        // Krok 6: Redirecty
        $redirectStep = $this->analyzeRedirects($methodStep);
        $flow['steps'][] = $redirectStep;

        // Krok 7: View
        $viewStep = $this->analyzeView($methodStep);
        $flow['steps'][] = $viewStep;
        $flow['issues'] = array_merge($flow['issues'], $viewStep['issues']);

        // Celkový status
        $flow['success'] = empty($flow['issues']);

        return $flow;
    }

    /**
     * Analyzuje bootstrap
     */
    private function analyzeBootstrap(): array
    {
        $indexFile = $this->rootPath . '/public/index.php';
        $bootstrapFile = $this->rootPath . '/bootstrap.php';

        $step = [
            'name' => 'Bootstrap',
            'files' => [],
            'issues' => []
        ];

        if (!file_exists($indexFile)) {
            $step['issues'][] = '❌ public/index.php neexistuje';
        } else {
            $step['files'][] = '✅ public/index.php';
        }

        if (!file_exists($bootstrapFile)) {
            $step['issues'][] = '❌ bootstrap.php neexistuje';
        } else {
            $step['files'][] = '✅ bootstrap.php';
        }

        // Kontrola core souborů
        $coreFiles = ['Router.php', 'Session.php', 'Config.php', 'Database.php', 'Auth.php'];
        foreach ($coreFiles as $file) {
            if (isset($this->projectMap['core'][basename($file, '.php')])) {
                $step['files'][] = "✅ app/Core/$file";
            } else {
                $step['issues'][] = "❌ app/Core/$file nenalezen";
            }
        }

        return $step;
    }

    /**
     * Analyzuje controller
     */
    private function analyzeController(string $controllerClass): array
    {
        $step = [
            'name' => 'Controller',
            'class' => $controllerClass,
            'exists' => false,
            'file' => null,
            'namespace' => null,
            'extends' => null,
            'issues' => []
        ];

        // Najdeme controller v mapě tříd
        if (isset($this->projectMap['classes'][$controllerClass])) {
            $info = $this->projectMap['classes'][$controllerClass];
            $step['exists'] = true;
            $step['file'] = $info['file'];
            $step['namespace'] = $info['namespace'];
            $step['extends'] = $info['extends'];

            // Kontrola dědění z core Controller
            if ($info['extends'] !== 'Controller') {
                $step['issues'][] = "⚠️ Controller nedědí z Controller (má: {$info['extends']})";
            }

            // Kontrola use statements
            $uses = $info['uses'] ?? [];
            $required = ['App\Core\Controller', 'App\Core\Flash', 'App\Core\Url'];
            foreach ($required as $req) {
                if (!in_array($req, $uses) && !in_array('\\' . $req, $uses)) {
                    $step['issues'][] = "⚠️ Chybí use $req";
                }
            }
        } else {
            // Zkusíme najít podle názvu souboru
            $className = substr($controllerClass, strrpos($controllerClass, '\\') + 1);
            if (isset($this->projectMap['controllers'][$className])) {
                $step['exists'] = true;
                $step['file'] = $this->projectMap['controllers'][$className];
                $step['issues'][] = "ℹ️ Controller nalezen, ale není v mapě tříd (možná chybí namespace?)";
            }
        }

        return $step;
    }

    /**
     * Analyzuje metodu v controlleru
     */
    private function analyzeMethod(array $controllerStep, string $methodName): array
    {
        $step = [
            'name' => 'Metoda',
            'method' => $methodName,
            'exists' => false,
            'params' => '',
            'return_type' => '',
            'code' => '',
            'issues' => []
        ];

        if (!$controllerStep['exists'] || !isset($controllerStep['file'])) {
            $step['issues'][] = "❌ Nelze analyzovat metodu - controller neexistuje";
            return $step;
        }

        $content = file_get_contents($controllerStep['file']);

        // Najdeme metodu
        $pattern = '/function\s+' . preg_quote($methodName) . '\s*\(([^)]*)\)(?:\s*:\s*([^{;]+))?\s*(?:\{|;)/';
        if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            $step['exists'] = true;
            $step['params'] = trim($matches[1][0] ?? '');
            $step['return_type'] = trim($matches[2][0] ?? '');

            // Extrahujeme tělo metody
            $startPos = $matches[0][1];
            $step['code'] = $this->extractMethodBody($content, $startPos);

            // Kontrola parametrů
            if (!empty($step['params']) && !preg_match('/\w+\s+\$\w+/', $step['params'])) {
                $step['issues'][] = "⚠️ Parametry nemají typy";
            }

            // Kontrola návratového typu
            if (empty($step['return_type'])) {
                $step['issues'][] = "⚠️ Metoda nemá návratový typ";
            }
        }

        return $step;
    }

    /**
     * Extrahuje tělo metody
     */
    private function extractMethodBody(string $content, int $startPos): string
    {
        $length = strlen($content);
        $braceCount = 0;
        $inBody = false;
        $body = '';

        for ($i = $startPos; $i < $length; $i++) {
            $char = $content[$i];

            if ($char === '{') {
                $braceCount++;
                $inBody = true;
            } elseif ($char === '}') {
                $braceCount--;
                if ($braceCount === 0 && $inBody) {
                    $body .= $char;
                    break;
                }
            }

            if ($inBody) {
                $body .= $char;
            }
        }

        return $body;
    }

    /**
     * Analyzuje volání modelů
     */
    private function analyzeModelCalls(array $methodStep): array
    {
        $step = [
            'name' => 'Modely',
            'calls' => [],
            'issues' => []
        ];

        if (!$methodStep['exists'] || empty($methodStep['code'])) {
            return $step;
        }

        $code = $methodStep['code'];

        // Hledání $this->model('Nazev')
        if (preg_match_all('/\$this->model\([\'"]([^\'"]+)[\'"]\)/', $code, $matches)) {
            foreach ($matches[1] as $model) {
                $modelFile = $model . '.php';
                $exists = isset($this->projectMap['models'][$model]);

                $step['calls'][] = [
                    'type' => 'model()',
                    'name' => $model,
                    'exists' => $exists
                ];

                if (!$exists) {
                    $step['issues'][] = "❌ Model '$model' neexistuje (voláno \$this->model())";
                }
            }
        }

        // Hledání new Model()
        if (preg_match_all('/new\s+([A-Z][a-zA-Z0-9_]+)\s*\(/', $code, $matches)) {
            foreach ($matches[1] as $model) {
                if (!in_array($model, ['self', 'static', 'parent'])) {
                    $exists = isset($this->projectMap['models'][$model]) ||
                              isset($this->projectMap['classes']['App\\Models\\' . $model]);

                    $step['calls'][] = [
                        'type' => 'new',
                        'name' => $model,
                        'exists' => $exists
                    ];

                    if (!$exists) {
                        $step['issues'][] = "ℹ️ Volání new $model() - není v modelech (možná service?)";
                    }
                }
            }
        }

        return $step;
    }

    /**
     * Analyzuje Flash zprávy
     */
    private function analyzeFlashCalls(array $methodStep): array
    {
        $step = [
            'name' => 'Flash zprávy',
            'calls' => [],
            'issues' => []
        ];

        if (!$methodStep['exists'] || empty($methodStep['code'])) {
            return $step;
        }

        if (preg_match_all('/Flash::(success|error|info|warning)\(/', $methodStep['code'], $matches)) {
            foreach ($matches[1] as $type) {
                $step['calls'][] = $type;
            }

            // Kontrola existence Flash třídy
            if (!isset($this->projectMap['core']['Flash'])) {
                $step['issues'][] = "❌ Flash třída neexistuje v app/Core/";
            }
        }

        return $step;
    }

    /**
     * Analyzuje redirecty
     */
    private function analyzeRedirects(array $methodStep): array
    {
        $step = [
            'name' => 'Redirecty',
            'calls' => [],
            'issues' => []
        ];

        if (!$methodStep['exists'] || empty($methodStep['code'])) {
            return $step;
        }

        if (preg_match_all('/Url::redirect\(([^)]+)\)/', $methodStep['code'], $matches)) {
            foreach ($matches[1] as $redirect) {
                $step['calls'][] = trim($redirect, '\'"');
            }

            // Kontrola existence Url třídy
            if (!isset($this->projectMap['core']['Url'])) {
                $step['issues'][] = "❌ Url třída neexistuje v app/Core/";
            }
        }

        return $step;
    }

    /**
     * Analyzuje view
     */
    private function analyzeView(array $methodStep): array
    {
        $step = [
            'name' => 'View',
            'render' => null,
            'variables' => [],
            'issues' => []
        ];

        if (!$methodStep['exists'] || empty($methodStep['code'])) {
            return $step;
        }

        $code = $methodStep['code'];

        // Hledání render
        if (preg_match('/\$this->render\([\'"]([^\'"]+)[\'"]\)/', $code, $matches)) {
            $viewName = $matches[1];
            $step['render'] = $viewName;

            // Kontrola existence view
            $viewFound = false;
            foreach ($this->projectMap['views'] as $vName => $vPath) {
                if (str_contains($vName, $viewName) || $vName === $viewName) {
                    $viewFound = true;
                    $step['view_file'] = $vPath;
                    break;
                }
            }

            if (!$viewFound) {
                $step['issues'][] = "❌ View '$viewName' neexistuje";
            }
        }

        // Hledání view proměnných
        if (preg_match_all('/\$this->view->(\w+)\s*=/', $code, $matches)) {
            $step['variables'] = $matches[1];
        }

        return $step;
    }

    /**
     * FÁZE 3: Generování HTML reportu
     */
    public function generateHtmlReport(): string
    {
        $html = '<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Flow Analyzer - Kompletní report</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #1a1a1a;
            color: #e0e0e0;
            padding: 20px;
        }
        .container { max-width: 1600px; margin: 0 auto; }

        h1 { color: #fff; margin-bottom: 30px; }
        h2 { color: #3498db; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-top: 30px; }
        h3 { color: #e0e0e0; margin: 20px 0 10px; }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #2d2d2d;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            border-left: 4px solid #3498db;
        }

        .stat-value {
            font-size: 36px;
            font-weight: bold;
            color: #3498db;
        }

        .stat-label {
            color: #aaa;
            font-size: 14px;
            text-transform: uppercase;
        }

        .project-tree {
            background: #2d2d2d;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            max-height: 400px;
            overflow-y: auto;
            font-family: monospace;
        }

        .tree-item {
            margin-left: 20px;
            cursor: pointer;
        }

        .tree-item.dir {
            color: #f1c40f;
        }

        .tree-item.file {
            color: #2ecc71;
        }

        .tree-item.php:before {
            content: "🐘 ";
        }

        .route-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
        }

        .route-button {
            background: #2d2d2d;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            color: #e0e0e0;
            cursor: pointer;
            font-size: 12px;
            border-left: 3px solid #3498db;
        }

        .route-button:hover {
            background: #3d3d3d;
        }

        .route-button.active {
            background: #3498db;
            color: white;
        }

        .route-button.has-issues {
            border-left-color: #e74c3c;
        }

        .flow-container {
            background: #2d2d2d;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }

        .step {
            margin: 15px 0;
            padding: 15px;
            background: #363636;
            border-radius: 8px;
            border-left: 4px solid #7f8c8d;
        }

        .step.success { border-left-color: #2ecc71; }
        .step.warning { border-left-color: #f39c12; }
        .step.error { border-left-color: #e74c3c; }

        .step-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            cursor: pointer;
        }

        .step-title {
            font-size: 16px;
            font-weight: bold;
            color: #fff;
        }

        .step-status {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .status-ok { background: #2ecc71; color: white; }
        .status-warning { background: #f39c12; color: white; }
        .status-error { background: #e74c3c; color: white; }

        .step-content {
            margin-top: 10px;
            padding-left: 20px;
            display: none;
        }

        .step-content.active {
            display: block;
        }

        .code-block {
            background: #1e1e1e;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            white-space: pre-wrap;
            margin: 10px 0;
            color: #d4d4d4;
        }

        .issues-list {
            background: #3d2b2b;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .issue-item {
            color: #e74c3c;
            margin: 5px 0;
        }

        .warning-item {
            color: #f39c12;
            margin: 5px 0;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            margin-right: 5px;
        }

        .badge-model { background: #8e44ad; color: white; }
        .badge-view { background: #27ae60; color: white; }
        .badge-flash { background: #e67e22; color: white; }

        .filter-input {
            background: #363636;
            border: 1px solid #444;
            color: #e0e0e0;
            padding: 10px;
            border-radius: 5px;
            width: 300px;
            margin-bottom: 20px;
        }

        .filter-input:focus {
            outline: none;
            border-color: #3498db;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 ROUTE FLOW ANALYZER - KOMPLETNÍ REPORT</h1>';

        // Statistiky
        $totalRoutes = count($this->projectMap['routes']);
        $totalControllers = count($this->projectMap['controllers']);
        $totalModels = count($this->projectMap['models']);
        $totalViews = count($this->projectMap['views']);
        $totalClasses = count($this->projectMap['classes']);

        $successRoutes = count(array_filter($this->flowReport, fn($f) => $f['success']));
        $issueRoutes = $totalRoutes - $successRoutes;

        $html .= '
        <div class="stats">
            <div class="stat-card">
                <div class="stat-value">' . $totalRoutes . '</div>
                <div class="stat-label">Rout</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">' . $successRoutes . '</div>
                <div class="stat-label">OK rout</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">' . $issueRoutes . '</div>
                <div class="stat-label">S problémy</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">' . $totalControllers . '</div>
                <div class="stat-label">Controllerů</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">' . $totalModels . '</div>
                <div class="stat-label">Modelů</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">' . $totalViews . '</div>
                <div class="stat-label">View</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">' . $totalClasses . '</div>
                <div class="stat-label">Tříd</div>
            </div>
        </div>';

        // Strom projektu
        $html .= '<h2>📁 Strom projektu</h2>';
        $html .= '<div class="project-tree" id="projectTree">';
        $html .= $this->renderTree($this->projectMap['structure']);
        $html .= '</div>';

        // Seznam rout
        $html .= '<h2>📋 Routy</h2>';
        $html .= '<input type="text" class="filter-input" id="routeFilter" placeholder="Filtrovat routy...">';
        $html .= '<div class="route-list" id="routeList">';

        foreach ($this->flowReport as $index => $flow) {
            $route = $flow['route'];
            $hasIssues = !$flow['success'];
            $activeClass = $index === 0 ? 'active' : '';
            $issueClass = $hasIssues ? 'has-issues' : '';

            $html .= '<button class="route-button ' . $activeClass . ' ' . $issueClass . '" onclick="showRoute(' . $index . ')">';
            $html .= $route['method'] . ' ' . htmlspecialchars($route['path']);
            $html .= '<br><small>' . $this->shortClassName($route['controller']) . '@' . $route['action'] . '</small>';
            $html .= '</button>';
        }

        $html .= '</div>';

        // Flow pro každou routu
        $html .= '<div id="flowContainer">';
        foreach ($this->flowReport as $index => $flow) {
            $display = $index === 0 ? 'block' : 'none';
            $html .= $this->renderFlow($flow, $index, $display);
        }
        $html .= '</div>';

        $html .= '
    </div>

    <script>
        function showRoute(index) {
            // Skrýt všechny flow
            document.querySelectorAll("[id^=flow-]").forEach(el => {
                el.style.display = "none";
            });

            // Zobrazit vybraný flow
            document.getElementById("flow-" + index).style.display = "block";

            // Aktivovat tlačítko
            document.querySelectorAll(".route-button").forEach((btn, i) => {
                if (i === index) {
                    btn.classList.add("active");
                } else {
                    btn.classList.remove("active");
                }
            });
        }

        function toggleStep(stepId) {
            const content = document.getElementById(stepId);
            content.classList.toggle("active");
        }

        function toggleTreeItem(element) {
            const children = element.nextElementSibling;
            if (children && children.classList.contains("tree-children")) {
                children.style.display = children.style.display === "none" ? "block" : "none";
            }
        }

        // Filtrování rout
        document.getElementById("routeFilter").addEventListener("keyup", function() {
            const filter = this.value.toLowerCase();
            const buttons = document.querySelectorAll(".route-button");

            buttons.forEach(button => {
                const text = button.textContent.toLowerCase();
                if (text.includes(filter)) {
                    button.style.display = "inline-block";
                } else {
                    button.style.display = "none";
                }
            });
        });
    </script>
</body>
</html>';

        return $html;
    }

    /**
     * Vykreslí stromovou strukturu
     */
    private function renderTree(array $structure, int $depth = 0): string
    {
        $html = '';
        $items = array_keys($structure);
        sort($items);

        foreach ($items as $item) {
            $info = $structure[$item];
            $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $depth);
            $class = $info['type'] === 'dir' ? 'dir' : 'file';
            if ($info['type'] === 'file' && $info['ext'] === 'php') {
                $class .= ' php';
            }

            $html .= '<div class="tree-item ' . $class . '" onclick="toggleTreeItem(this)">';
            $html .= $indent . ($info['type'] === 'dir' ? '📁 ' : '📄 ') . basename($item);
            $html .= '</div>';

            if ($info['type'] === 'dir' && isset($info['children'])) {
                $html .= '<div class="tree-children" style="display:none;">';
                $html .= $this->renderTree($info['children'], $depth + 1);
                $html .= '</div>';
            }
        }

        return $html;
    }

    /**
     * Vykreslí flow pro jednu routu
     */
    private function renderFlow(array $flow, int $index, string $display): string
    {
        $route = $flow['route'];
        $html = '<div class="flow-container" id="flow-' . $index . '" style="display: ' . $display . ';">';

        $html .= '<h3>' . $route['method'] . ' ' . htmlspecialchars($route['path']) . '</h3>';
        $html .= '<p><strong>Controller:</strong> ' . $route['controller'] . '::' . $route['action'] . '()</p>';

        if (!empty($route['roles'])) {
            $html .= '<p><strong>Role:</strong> ' . implode(', ', $route['roles']) . '</p>';
        }

        if ($route['title']) {
            $html .= '<p><strong>Title:</strong> ' . htmlspecialchars($route['title']) . '</p>';
        }

        // Jednotlivé kroky
        foreach ($flow['steps'] as $stepIndex => $step) {
            $hasIssues = !empty($step['issues']);
            $stepClass = $hasIssues ? 'error' : 'success';
            $stepId = 'step-' . $index . '-' . $stepIndex;

            $html .= '<div class="step ' . $stepClass . '">';
            $html .= '<div class="step-header" onclick="toggleStep(\'' . $stepId . '\')">';
            $html .= '<span class="step-title">' . $step['name'] . '</span>';
            $html .= '<span class="step-status status-' . $stepClass . '">';
            $html .= count($step['issues'] ?? []) . ' problémů';
            $html .= '</span>';
            $html .= '</div>';

            $html .= '<div class="step-content" id="' . $stepId . '">';

            // Obsah podle typu kroku
            switch ($step['name']) {
                case 'Bootstrap':
                    if (!empty($step['files'])) {
                        $html .= '<div class="code-block">' . implode('<br>', $step['files']) . '</div>';
                    }
                    break;

                case 'Controller':
                    $html .= '<p><strong>Soubor:</strong> ' . ($step['file'] ?? 'Nenalezen') . '</p>';
                    if ($step['namespace']) {
                        $html .= '<p><strong>Namespace:</strong> ' . $step['namespace'] . '</p>';
                    }
                    if ($step['extends']) {
                        $html .= '<p><strong>Dědí z:</strong> ' . $step['extends'] . '</p>';
                    }
                    break;

                case 'Metoda':
                    $html .= '<p><strong>Parametry:</strong> ' . ($step['params'] ?: 'žádné') . '</p>';
                    $html .= '<p><strong>Návratový typ:</strong> ' . ($step['return_type'] ?: 'není') . '</p>';
                    break;

                case 'Modely':
                    if (!empty($step['calls'])) {
                        foreach ($step['calls'] as $call) {
                            $icon = $call['exists'] ? '✅' : '❌';
                            $html .= '<div>' . $icon . ' ' . $call['type'] . ': ' . $call['name'] . '</div>';
                        }
                    }
                    break;

                case 'Flash zprávy':
                    if (!empty($step['calls'])) {
                        foreach ($step['calls'] as $type) {
                            $html .= '<div><span class="badge badge-flash">Flash</span> ' . $type . '</div>';
                        }
                    }
                    break;

                case 'Redirecty':
                    if (!empty($step['calls'])) {
                        foreach ($step['calls'] as $redirect) {
                            $html .= '<div>➜ ' . htmlspecialchars($redirect) . '</div>';
                        }
                    }
                    break;

                case 'View':
                    if ($step['render']) {
                        $html .= '<p><strong>Renderuje:</strong> ' . $step['render'] . '</p>';
                        if (!empty($step['variables'])) {
                            $html .= '<p><strong>Proměnné:</strong> ' . implode(', ', $step['variables']) . '</p>';
                        }
                    }
                    break;
            }

            // Problémy
            if (!empty($step['issues'])) {
                $html .= '<div class="issues-list">';
                foreach ($step['issues'] as $issue) {
                    $html .= '<div class="issue-item">⚠️ ' . htmlspecialchars($issue) . '</div>';
                }
                $html .= '</div>';
            }

            $html .= '</div>'; // step-content
            $html .= '</div>'; // step
        }

        // Celkové problémy
        if (!empty($flow['issues'])) {
            $html .= '<div class="issues-list"><h4>Celkové problémy:</h4>';
            foreach ($flow['issues'] as $issue) {
                $html .= '<div class="issue-item">❌ ' . htmlspecialchars($issue) . '</div>';
            }
            $html .= '</div>';
        }

        $html .= '</div>'; // flow-container
        return $html;
    }

    /**
     * Zkrátí název třídy
     */
    private function shortClassName(string $fullClass): string
    {
        $parts = explode('\\', $fullClass);
        return end($parts);
    }
}

// HLAVNÍ SPUŠTĚNÍ
echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║     KOMPLETNÍ ROUTE FLOW ANALYZER S MAPOU PROJEKTU          ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";

// FÁZE 1: Vytvoření mapy projektu
$mapper = new ProjectMapper(__DIR__);
$projectMap = $mapper->createMap();

// FÁZE 2: Analýza flow pro všechny routy
$analyzer = new RouteFlowAnalyzer($projectMap, __DIR__);
$flowReport = $analyzer->analyzeAllRoutes();

// FÁZE 3: Generování HTML reportu
echo "\n📄 Generuji HTML report...\n";
$html = $analyzer->generateHtmlReport();
$htmlFile = __DIR__ . '/route-flow-report.html';
file_put_contents($htmlFile, $html);

echo "   ✅ HTML report: $htmlFile\n";
echo "\n✅ HOTOVO! Otevřete route-flow-report.html v prohlížeči.\n\n";