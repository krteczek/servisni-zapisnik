<?php
declare(strict_types=1);

return [

    'admin' => [
        'host'     => 'localhost',
        'dbname'   => 'admin',
        'user'     => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
        'prefix'   => '',
    ],

    'work' => [
        'host'     => 'localhost',
        // dbname se SEM NEDÁVÁ – vybírá se dynamicky
        'user'     => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
        'prefix'   => '',
    ],

];