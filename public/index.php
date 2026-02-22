<?php
declare(strict_types=1);

ob_start();

define('APP_ENV', file_exists(__DIR__ . '/../.dev') ? 'dev' : 'prod');
define('APP_DEBUG', APP_ENV === 'dev');

define('BASE_PATH', '/servisni-zapisnik/public');
//echo phpversion();exit;
require dirname(__DIR__) . '/bootstrap.php';
require dirname(__DIR__) . '/app/Core/helpers.php';

use App\Core\Router;
use App\Core\Session;
use App\Core\Config;
use App\Core\Logger;
use App\Core\LoggerHolder;
use App\Core\Database;
use App\Core\Auth;

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
if (str_starts_with($uri, BASE_PATH)) {
    $uri = substr($uri, strlen(BASE_PATH));
}

$uri = $uri ?: '/';

$routes = Config::get('routes');

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
    if(APP_ENV === 'dev') 
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
