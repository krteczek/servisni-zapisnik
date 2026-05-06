<?php
declare(strict_types=1);

return [

    'admin' => [
        'host'     => 'db.dw323.webglobe.com',
        'dbname'   => 'krteczek_cz',
        'user'     => 'krteczek_cz',
        'password' => 'CJ1uMRWu',
        'charset'  => 'utf8mb4',
        'prefix'   => '',
    ],

    'work' => [
        'host'     => 'db.dw323.webglobe.com',
        // dbname se SEM NEDÁVÁ – vybírá se dynamicky
        'user'     => 'krteczek_cz',
        'password' => 'CJ1uMRWu',
        'charset'  => 'utf8mb4',
        'prefix'   => '',
    ],

];
/**
<?php
declare(strict_types=1);

$path = dirname(__DIR__, 3) . '/bo.database.php';

if (!file_exists($path)) {
    throw new RuntimeException('Missing config file: ' . $path);
}
$ddd = require $path; 
return $ddd;
*/