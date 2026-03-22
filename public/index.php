<?php
declare(strict_types=1);

ob_start();

define('BASE_PATH', '/servisni-zapisnik/public');
//echo phpversion();exit;
require dirname(__DIR__) . '/bootstrap.php';

App\Core\ExceptionHandler::register();

require dirname(__DIR__) . '/app/Core/helpers.php';

use App\Core\Router;
use App\Core\Session;
use App\Core\Config;
use App\Core\Logger;
use App\Core\LoggerHolder;
use App\Core\Database;
use App\Core\Auth;
use App\Core\Roles;
use App\Services\Guards\BanService;

$appEnv = Config::get('app.env');
$appDebug = Config::get('app.debug');
$appBasePath = Config::get('app.base_path');

if(BanService::isBanned() === true)
{
	echo "Příliš mnoho neplatných pokusů. Přístup je Vám dočasně odepřen. ";
	exit;
}
//error_reporting(Config::get('app.error_reporting'));
ini_set('display_errors', Config::get('app.display_errors') ? '0' : '0');
// -------------------------------------------------
// Logger
// -------------------------------------------------
LoggerHolder::set(new Logger(__DIR__ . '/../storage/logs/app.log'));

// -------------------------------------------------
// Session
// -------------------------------------------------
Session::start();
//var_dump($_SESSION);
// -------------------------------------------------
// ❗ PRVNÍ A NATVRDÉ PŘIPOJENÍ K DB = ADMIN
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
/*
var_dump([
    'auth_role' => Auth::role(),
    'session_global_role' => $_SESSION['user']['global_role'] ?? null,
    'isManagement' => Roles::isManagement(Auth::role()),
]);
*/
try {
    $router = new Router($routes);
    echo $router->dispatch($uri, $_SERVER['REQUEST_METHOD']);
} catch (Throwable $e) {

	$message = '
	message: ' . $e->getMessage() . '
	file: ' . $e->getFile() . '
	line: ' . $e->getLine();
    // úplné selhání frameworku
    LoggerHolder::get()->critical($message);

    //http_response_code(500);
    if($appEnv === 'dev') 
    {
    	echo "<h1>Framework crash</h1>
    	<pre>
    	";
    	var_dump($message);
    	
    	echo "
    	</pre>
    	";
    } else {
    echo 'Kritická chyba aplikace.';
 }
}
