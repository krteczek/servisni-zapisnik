<?php
declare(strict_types=1);

ob_start();

//define('BASE_PATH', '/servisni-zapisnik/public');
//echo phpversion();exit;
require dirname(__DIR__) . '/bootstrap.php';


use App\Core\Response;
use App\Core\LoggerHolder;
use App\Core\Logger;
use App\Core\ExceptionHandler;
// -------------------------------------------------
// Ostatní Use statement
// -------------------------------------------------
use App\Services\Recurrings\RecurringRunner;
use App\Core\Router;
use App\Core\Session;
use App\Core\Config;
use App\Core\Database;
use App\Core\Auth;
use App\Core\Url;
use App\Services\Guards\BanService;


// -------------------------------------------------
// Security Headers
// -------------------------------------------------
Response::applySecurityHeaders();

// -------------------------------------------------
// Logger
// -------------------------------------------------
LoggerHolder::set(new Logger(__DIR__ . '/../storage/logs/app.log'));

// -------------------------------------------------
// ExceptionHandler
// -------------------------------------------------

ExceptionHandler::register();



require dirname(__DIR__) . '/app/Core/helpers.php';

$appEnv = Config::get('app.env');
$appDebug = Config::get('app.debug');
$appBasePath = Config::get('app.base_path');


// -------------------------------------------------
// RecurringRunner: spouští cronování reccuring tasků
// -------------------------------------------------
RecurringRunner::run();


// -------------------------------------------------
// BanService: pokud se uživatel ošklivě chová,
// nedostane propustku
// -------------------------------------------------


if (BanService::isBanned() === true) {

    ob_end_clean();

    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    header('X-Robots-Tag: noindex, nofollow');

    echo '
<!doctype html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <title>Přístup odepřen</title>
</head>
<body>
    <h1>Přístup odepřen</h1>
    <p>Bylo zaznamenáno vícenásobné nevhodné chování. Přístup je Vám dočasně odepřen.</p>
    <h1>Access Denied</h1>
<p>Repeated inappropriate behavior has been detected. Your access has been temporarily restricted.</p>
</body>
</html>';

    exit;
}


// -------------------------------------------------
// Session
// -------------------------------------------------
Session::start();

// -------------------------------------------------
// ❗ PRVNÍ A NATVRDO PŘIPOJENÍ K DB = ADMIN
// -------------------------------------------------
//Database::admin();

if (Auth::check() && Session::has('user.company_db_name')) {
    Database::useWorkDatabase(
        Session::get('user.company_db_name')
    );
}
// -------------------------------------------------
// Routing
// -------------------------------------------------
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

$uri = parse_url($requestUri, PHP_URL_PATH);

$uri = '/' . ltrim($uri, '/');
$uri = rtrim($uri, '/') ?: '/';

if (!is_string($uri) || $uri === '') {

    LoggerHolder::get()->warning(
        'Invalid REQUEST_URI received',
        [
            'request_uri' => $requestUri,
        ]
    );

    $uri = '/';
}

if ($appBasePath !== ''
    && str_starts_with($uri, $appBasePath)
) {
    $uri = substr($uri, strlen($appBasePath));
}

$uri = $uri ?: '/';
var_dump([
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? null,
    'base' => Url::to('/'),
    'register' => Url::to('/register'),
]);
exit;

$routes = Config::get('routes');

    $router = new Router($routes);
    echo $router->dispatch($uri, $_SERVER['REQUEST_METHOD']);
    
ob_end_flush();
