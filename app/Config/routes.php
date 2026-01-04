<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\UserController;

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


[
    'method' => 'GET',
    'path' => '/admin/users',
    'action' => [UserController::class, 'index'],
    'auth' => true,
    'roles' => ['admin'],
],

[
    'method' => 'GET',
    'path' => '/admin/users/create',
    'action' => [UserController::class, 'create'],
    'auth' => true,
    'roles' => ['admin'],
],

[
    'method' => 'POST',
    'path' => '/admin/users/store',
    'action' => [UserController::class, 'store'],
    'auth' => true,
    'roles' => ['admin'],
],
    [
        'method' => 'GET',
        'path' => '/users/create',
        'action' => [UserController::class, 'create'],
        'auth' => true,
        'roles' => ['admin'],
    ],
    [
        'method' => 'POST',
        'path' => '/users',
        'action' => [UserController::class, 'store'],
        'auth' => true,
        'roles' => ['admin'],
    ],

];
