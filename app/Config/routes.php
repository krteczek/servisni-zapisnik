<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\UserController;
use App\Controllers\TeamController;

return [

    // ROOT (není v menu!)
    [
        'method' => 'GET',
        'path'   => '/',
        'action' => [AuthController::class, 'root'],
        'auth'   => false,
    ],

    // LOGIN
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

    // DASHBOARD
    [
        'method'  => 'GET',
        'path'    => '/dashboard',
        'action'  => [DashboardController::class, 'index'],
        'auth'    => true,
        'roles'   => ['admin', 'mistr', 'predak', 'monter'],
        'menu'    => 'Dashboard',
        'section' => 'dashboard',
        'title'   => 'Úkoly',
    ],

    // USERS – přehled
    [
        'method'  => 'GET',
        'path'    => '/users',
        'action'  => [UserController::class, 'index'],
        'auth'    => true,
        'roles'   => ['admin', 'mistr'],
        'menu'    => 'Uživatelé',
        'submenu' => 'Přehled',
        'section' => 'users',
        'title'   => 'Uživatelé > Přehled',
    ],

    // USERS – create
    [
        'method'  => 'GET',
        'path'    => '/users/create',
        'action'  => [UserController::class, 'create'],
        'auth'    => true,
        'roles'   => ['admin'],
        'submenu' => 'Přidat uživatele',
        'section' => 'users',
        'title'   => 'Uživatelé > Přidat uživatele',
    ],

    [
        'method' => 'POST',
        'path'   => '/users',
        'action' => [UserController::class, 'store'],
        'auth'   => true,
        'roles'  => ['admin'],
    ],
[
    'method' => 'GET',
    'path'   => '/users/{id}/edit',
    'action' => [UserController::class, 'editForm'],
    'auth'   => true,
    'roles'  => ['admin'],
    'title'=> 'Uživatelé > Upravit uživatele',
    'section'=> 'users',
],

[
    'method' => 'POST',
    'path'   => '/users/{id}/edit',
    'action' => [UserController::class, 'edit'],
    'auth'   => true,
    'roles'  => ['admin'],
    'section'=> 'users',
],
[
    'method'  => 'GET',
    'path'    => '/users/{id}/password',
    'action'  => [UserController::class, 'passwordForm'],
    'auth'    => true,
    'roles'   => ['admin'],
    'section' => 'users',
    'title'   => 'Změna hesla',
],

[
    'method'  => 'POST',
    'path'    => '/users/{id}/password',
    'action'  => [UserController::class, 'updatePassword'],
    'auth'    => true,
    'roles'   => ['admin'],
],



// TEAMS – přehled
[
    'method'  => 'GET',
    'path'    => '/teams',
    'action'  => [TeamController::class, 'index'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'menu'    => 'Týmy',
    'submenu' => 'Přehled týmů',
    'section' => 'teams',
    'title'   => 'Týmy',
],

// TEAMS – create
[
    'method'  => 'GET',
    'path'    => '/teams/create',
    'action'  => [TeamController::class, 'create'],
    'auth'    => true,
    'roles'   => ['admin'],
    'submenu' => 'Vytvořit tým',
    'section' => 'teams',
    'title'   => 'Týmy -> Vytvořit tým',
],

[
    'method' => 'POST',
    'path'   => '/teams',
    'action' => [TeamController::class, 'store'],
    'auth'   => true,
    'roles'  => ['admin'],
],

// TEAMS – detail + členové
[
    'method'  => 'GET',
    'path'    => '/teams/{id}/edit',
    'action'  => [TeamController::class, 'edit'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'teams',
    'title'   => 'Týmy -> Detail týmu',
],

// TEAMS – update + členové
[
    'method' => 'POST',
    'path'   => '/teams/{id}/edit',
    'action' => [TeamController::class, 'update'],
    'auth'   => true,
    'roles'  => ['admin'],
],


    // LOGOUT (POST!) Nechat poslední, aby byl poslední i ve výpisu
    [
        'method' => 'POST',
        'path'   => '/logout',
        'action' => [AuthController::class, 'logout'],
        'auth'   => true,
        'menu'   => 'Odhlásit',
    ],
];
