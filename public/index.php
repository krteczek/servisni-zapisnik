<?php
declare(strict_types=1);

ob_start();

define('APP_ENV', file_exists(__DIR__ . '/../.dev') ? 'dev' : 'prod');
define('APP_DEBUG', APP_ENV === 'dev');

define('BASE_PATH', '/servisni-zapisnik/public');

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

    LoggerHolder::get()->error(
        $e->getMessage(),
        [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]
    );

    if (APP_DEBUG) {
        $text = '
<h1>Něco se posralo, koukni se co a koukej to spravit</h1>
<div>
<pre>' . $e . '</pre>
</div>
';
    } else {
        $text = '
<h1>Něco se pokazilo</h1>
<p>Omlouváme se, došlo k technické chybě.</p>
<p>Situace byla zaznamenána a budeme se jí zabývat.</p>
<p>Zkuste prosím akci zopakovat později.</p>
<p>V případě potřeby můžete kontaktovat administrátora na telefonu: +420 704 142 511.</p>
';
    }

    require dirname(__DIR__) . '/app/Views/errors/500.php';
    http_response_code(500);
}