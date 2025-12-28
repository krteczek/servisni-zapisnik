<?php
declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;

return [
    [
        'method' => 'GET',
        'path' => '/',
        'handler' => [AuthController::class, 'loginForm'],
        'roles' => null
    ],
    [
        'method' => 'POST',
        'path' => '/login',
        'handler' => [AuthController::class, 'login'],
        'roles' => null
    ],
    [
        'method' => 'GET',
        'path' => '/dashboard',
        'handler' => [DashboardController::class, 'index'],
        'roles' => ['admin', 'mistr', 'predak', 'monter']
    ],
    [
        'method' => 'GET',
        'path' => '/logout',
        'handler' => [AuthController::class, 'logout'],
        'roles' => ['admin', 'mistr', 'predak', 'monter']
    ],
];