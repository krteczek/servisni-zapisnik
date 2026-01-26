<?php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\UserController;
use App\Controllers\TeamController;
use App\Controllers\AdminController;
use App\Controllers\WorkOrderController;
use App\Controllers\AuditLogController;
use App\Controllers\SystemController;


return [

[
    'path'   => '/system',
    'action' => [SystemController::class, 'index'],
    'roles'  => ['root'],
    'menu'   => 'Admin firemních účtů',
    'submenu'=> '',
    'section' => 'system',
    'title'   => 'Systém > výpis Firem',
        'auth'   => true,

],

[
    'path'   => '/admin/switch-role/{role}',
    'method' => 'GET',
    'action' => [AdminController::class, 'switchRole'],
    'roles'  => ['admin'],
        'auth'   => true,

],

[
    'path'   => '/admin/audit',
    'action' => [AuditLogController::class, 'index'],
    'roles'  => ['admin'],
    'menu'   => 'Administrace',
    'submenu'=> 'Audit log',
        'auth'   => true,

    'section' => 'admin',
    'title'   => 'Admin > Audit > Audit log',
],
[
    'path'   => '/admin/audit/{id}',
    'action' => [AuditLogController::class, 'detail'],
    'roles'  => ['admin'],
    'auth'   => true,

    'title'   => 'Admin > Audit > Detail auditního záznamu',],

    // ROOT (není v menu!)
[
    'method' => 'GET',
    'path'   => '/',
    'action' => [DashboardController::class, 'root'],
    'auth'   => true,
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
        'roles'   => ['root', 'admin', 'mistr', 'predak', 'monter'],
        'menu'    => 'Dashboard',
        'section' => 'dashboard',
        'title'   => 'Úkoly',
    ],
[
    'method'  => 'GET',
    'path'    => '/work-orders',
    'action'  => [WorkOrderController::class, 'index'],
    'auth'    => true,
    'roles'   => ['admin','mistr','predak'],
    'menu'    => 'Dashboard',
    'submenu' => 'Zakázky',
    'section' => 'dashboard',
    'title'   => 'Dashboard > Zakázky',
],

[
    'method'  => 'GET',
    'path'    => '/work-orders/create',
    'action'  => [WorkOrderController::class, 'createForm'],
    'auth'    => true,
    'roles'   => ['admin','mistr','predak'],
    'submenu' => 'Nová zakázka',
    'section' => 'dashboard',
    'title'   => 'Dashboard > Zakázky > Nová',
],

[
    'method' => 'POST',
    'path'   => '/work-orders/create',
    'action' => [WorkOrderController::class, 'create'],
    'auth'   => true,
    'roles'  => ['admin','mistr'],
],

[
    'method'  => 'GET',
    'path'    => '/work-orders/update/{id}',
    'action'  => [WorkOrderController::class, 'update'],
    'auth'    => true,
    'roles'   => ['admin','mistr'],
    'section' => 'dashboard',
    'title'   => 'Dashboard > Upravit zakázku',
],
[
    'method'  => 'POST',
    'path'    => '/work-orders/update/{id}',
    'action'  => [WorkOrderController::class, 'update'],
    'auth'    => true,
    'roles'   => ['admin','mistr'],
    'section' => 'dashboard',
    'title'   => 'Dashboard > Upravit zakázku',
],

[
    'method'  => 'GET',
    'path'    => '/work-orders/{id}',
    'action'  => [WorkOrderController::class, 'detail'],
    'auth'    => true,
    'roles'   => ['admin','mistr','predak','monter'],
    'section' => 'dashboard',
    'title'   => 'Dashboard > Zakázka',
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
        'path'   => '/users/create',
        'action' => [UserController::class, 'store'],
        'auth'   => true,
        'roles'  => ['admin'],
    ],
[
    'method' => 'GET',
    'path'   => '/users/{id}/edit',
    'action' => [UserController::class, 'edit'],
    'auth'   => true,
    'roles'  => ['admin', 'mistr'],
    'title'=> 'Uživatelé > Upravit uživatele',
    'section'=> 'users',
],

[
    'method' => 'POST',
    'path'   => '/users/{id}/edit',
    'action' => [UserController::class, 'update'],
    'auth'   => true,
    'roles'  => ['admin', 'mistr'],
    'section'=> 'users',
],
[
    'method'  => 'GET',
    'path'    => '/users/{id}/password',
    'action'  => [UserController::class, 'passwordForm'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'users',
    'title'   => 'Změna hesla',
],

[
    'method'  => 'POST',
    'path'    => '/users/{id}/password',
    'action'  => [UserController::class, 'updatePassword'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
],



// TEAMS – přehled
[
    'method'  => 'GET',
    'path'    => '/teams',
    'action'  => [TeamController::class, 'index'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'menu'    => 'Týmy',
    'submenu' => 'Aktivní',
    'section' => 'teams',
    'title'   => 'Přehled aktivních týmů',
],
[
    'method'  => 'GET',
    'path'    => '/teams/inactive',
    'action'  => [TeamController::class, 'inactive'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'menu'    => 'Týmy',
    'submenu' => 'Deaktivované',
    'section' => 'teams',
    'title'   => 'Přehled deaktivovaných týmů',
],

// TEAMS – změna aktivni/neaktivní (toggle)
[
    'method'  => 'GET',
    'path'    => '/teams/toggle/{id}',
    'action'  => [TeamController::class, 'toggle'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'teams',
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
    'path'   => '/teams/create',
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
