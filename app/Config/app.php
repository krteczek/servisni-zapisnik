<?php
declare(strict_types=1);

return [
    'env'             => 'dev',
    'debug'           => true,

    'base_path'       => '/public',

    'error_reporting' => E_ALL,

    'display_errors'  => true,

    'url'             => 'https://bo.krteczek.cz',
];
/*
<?php
declare(strict_types=1);

$path = dirname(__DIR__, 3) . '/bo.app.php';

if (!file_exists($path)) {
    throw new RuntimeException('Missing config file: ' . $path);
}

return require $path;

*/
