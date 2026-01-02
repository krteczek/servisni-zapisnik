<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;

return [
[
    'method' => 'GET',
    'path'   => '/',
    'action' => [AuthController::class, 'root'],
    'auth'   => false,
],
    [
        'method' => 'GET',
        'path'   => '/login',
        'action' => [AuthController::class, 'loginForm'],
        'auth'   => false,
    ],
    [
        'method' => 'POST',
        'path'   => '/login',
        'action' => [AuthController::class, 'login'],
        'auth'   => false,
    ],
[
    'method' => 'GET',
    'path'   => '/dashboard',
    'action' => [DashboardController::class, 'index'],
    'auth'   => true,
    'roles'  => ['admin', 'mistr', 'predak', 'monter'],
    'menu'   => 'Dashboard',
],



[
    'method' => 'GET',
    'path'   => '/users',
    'action' => [UserController::class, 'index'],
    'auth'   => true,
    'roles'  => ['admin', 'mistr'],
    'menu'   => 'Lidé',
],

[
    'method' => 'POST',
    'path'   => '/logout',
    'action' => [AuthController::class, 'logout'],
    'auth'   => true,
    'menu'   => 'Odhlásit',
],


];
