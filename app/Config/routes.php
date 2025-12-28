<?php

use App\Controllers\LoginController;
use App\Controllers\DashboardController;

return [
    [
        'method' => 'GET',
        'path'   => '/login',
        'action' => LoginController::class . '@show',
        'auth'   => false,
    ],
    [
        'method' => 'POST',
        'path'   => '/login',
        'action' => LoginController::class . '@login',
        'auth'   => false,
    ],
    [
        'method' => 'GET',
        'path'   => '/dashboard',
        'action' => DashboardController::class . '@index',
        'auth'   => true,
        'roles'  => ['admin', 'mistr', 'predak', 'monter'],
    ],
];