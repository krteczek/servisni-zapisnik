<?php
declare(strict_types=1);

ob_start();

//define('BASE_PATH', '/servisni-zapisnik/public');
//echo phpversion();exit;
require dirname(__DIR__) . '/bootstrap.php';

// -------------------------------------------------
// Logger
// -------------------------------------------------
use App\Core\LoggerHolder;
use App\Core\Logger;
LoggerHolder::set(new Logger(__DIR__ . '/../storage/logs/app.log'));

// -------------------------------------------------
// ExceptionHandler
// -------------------------------------------------
use App\Core\ExceptionHandler;
ExceptionHandler::register();


// -------------------------------------------------
// Ostatní Use statement
// -------------------------------------------------
use App\Core\Router;
use App\Core\Session;
use App\Core\Config;
use App\Core\Database;
use App\Core\Auth;
use App\Core\Roles;
use App\Services\Guards\BanService;

require dirname(__DIR__) . '/app/Core/helpers.php';

$appEnv = Config::get('app.env');
$appDebug = Config::get('app.debug');
$appBasePath = Config::get('app.base_path');

// -------------------------------------------------
// BanService: pokud se uživatel ošklivě chová,
// nedostane propustku
// -------------------------------------------------

if(BanService::isBanned() === true)
{
	echo "Bylo zaznamenáno vícenásobné nevhodné chování. Přístup je Vám dočasně odepřen. ";
	exit;
}


// -------------------------------------------------
// Session
// -------------------------------------------------
Session::start();

// -------------------------------------------------
// ❗ PRVNÍ A NATVRDO PŘIPOJENÍ K DB = ADMIN
// -------------------------------------------------
Database::admin();

if (Auth::check() && Session::has('user.company_db_name')) {
    Database::useWorkDatabase(
        Session::get('user.company_db_name')
    );
}
// -------------------------------------------------
// Routing
// -------------------------------------------------
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// odstranění base path
if (str_starts_with($uri, $appBasePath)) {
    $uri = substr($uri, strlen($appBasePath));
}

$uri = $uri ?: '/';

$routes = Config::get('routes');

    $router = new Router($routes);
    echo $router->dispatch($uri, $_SERVER['REQUEST_METHOD']);
    
ob_end_flush();
