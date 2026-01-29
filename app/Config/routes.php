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

/*
|--------------------------------------------------------------------------
| ROOT + AUTH
|--------------------------------------------------------------------------
*/

[
    'method' => 'GET',
    'path'   => '/',
    'action' => [DashboardController::class, 'root'],
    'auth'   => true,
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

/*
|--------------------------------------------------------------------------
| DASHBOARD / ÚKOLY
|--------------------------------------------------------------------------
*/

[
    'method'  => 'GET',
    'path'    => '/dashboard',
    'action'  => [DashboardController::class, 'index'],
    'auth'    => true,
    'roles'   => ['root', 'admin', 'mistr', 'predak', 'monter'],
    'menu'    => 'Úkoly',
    'section' => 'tasks',
    'title'   => 'Úkoly > Přehled',
],

/*
|--------------------------------------------------------------------------
| ZAKÁZKY
|--------------------------------------------------------------------------
*/

[
    'method'  => 'GET',
    'path'    => '/work-orders',
    'action'  => [WorkOrderController::class, 'index'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr', 'predak'],
    'menu'    => 'Zakázky',
    'submenu' => 'Přehled',
    'section' => 'workorders',
    'title'   => 'Zakázky > Přehled',
],

[
    'method'  => 'GET',
    'path'    => '/work-orders/create',
    'action'  => [WorkOrderController::class, 'createForm'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr', 'predak'],
    'submenu' => 'Nová zakázka',
    'section' => 'workorders',
    'title'   => 'Zakázky > Nová',
],

[
    'method' => 'POST',
    'path'   => '/work-orders/create',
    'action' => [WorkOrderController::class, 'create'],
    'auth'   => true,
    'roles'  => ['admin', 'mistr'],
],

[
    'method'  => 'GET',
    'path'    => '/work-orders/{id}',
    'action'  => [WorkOrderController::class, 'detail'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr', 'predak', 'monter'],
    'section' => 'workorders',
    'title'   => 'Zakázky > Detail',
],

[
    'method'  => 'GET',
    'path'    => '/work-orders/update/{id}',
    'action'  => [WorkOrderController::class, 'update'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'workorders',
    'title'   => 'Zakázky > Upravit',
],
[
    'method'  => 'POST',
    'path'    => '/work-orders/update/{id}',
    'action'  => [WorkOrderController::class, 'update'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
],

/*
|--------------------------------------------------------------------------
| UŽIVATELÉ
|--------------------------------------------------------------------------
*/

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

[
    'method'  => 'GET',
    'path'    => '/users/create',
    'action'  => [UserController::class, 'create'],
    'auth'    => true,
    'roles'   => ['admin'],
    'submenu' => 'Přidat uživatele',
    'section' => 'users',
    'title'   => 'Uživatelé > Nový',
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
    'section'=> 'users',
],

[
    'method' => 'POST',
    'path'   => '/users/{id}/edit',
    'action' => [UserController::class, 'update'],
    'auth'   => true,
    'roles'  => ['admin', 'mistr'],
],

/*
|--------------------------------------------------------------------------
| TÝMY
|--------------------------------------------------------------------------
*/

[
    'method'  => 'GET',
    'path'    => '/teams',
    'action'  => [TeamController::class, 'index'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'menu'    => 'Týmy',
    'submenu' => 'Aktivní',
    'section' => 'teams',
    'title'   => 'Týmy > Aktivní',
],

[
    'method'  => 'GET',
    'path'    => '/teams/inactive',
    'action'  => [TeamController::class, 'inactive'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'submenu' => 'Deaktivované',
    'section' => 'teams',
    'title'   => 'Týmy > Deaktivované',
],

[
    'method'  => 'GET',
    'path'    => '/teams/create',
    'action'  => [TeamController::class, 'create'],
    'auth'    => true,
    'roles'   => ['admin'],
    'submenu' => 'Vytvořit tým',
    'section' => 'teams',
    'title'   => 'Týmy > Nový',
],

[
    'method'  => 'GET',
    'path'    => '/teams/toggle/{id}',
    'action'  => [TeamController::class, 'toggle'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'teams',
],

/*-------------------------------------
Přepínaní rolí u admina
-------------------------------------**/

[
    'path'   => '/admin/switch-role/{role}',
    'method' => 'GET',
    'action' => [AdminController::class, 'switchRole'],
    'roles'  => ['admin'],
        'auth'   => true,

],



/*
|--------------------------------------------------------------------------
| ADMIN / SYSTEM
|--------------------------------------------------------------------------
*/

[
    'method' => 'GET',
    'path'   => '/system',
    'action' => [SystemController::class, 'index'],
    'roles'  => ['root'],
    'menu'   => 'Administrace',
    'submenu'=> 'Systém',
    'section'=> 'system',
    'auth'   => true,
],

[
    'method' => 'GET',
    'path'   => '/admin/audit',
    'action' => [AuditLogController::class, 'index'],
    'roles'  => ['admin'],
    'menu'   => 'Administrace',
    'submenu'=> 'Audit log',
    'section'=> 'admin',
    'auth'   => true,
],

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

[
    'method' => 'POST',
    'path'   => '/logout',
    'action' => [AuthController::class, 'logout'],
    'auth'   => true,
    'menu'   => 'Odhlásit',
],

];
