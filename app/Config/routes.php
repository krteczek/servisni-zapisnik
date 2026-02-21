<?php
declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\UserController;
use App\Controllers\TeamController;
use App\Controllers\AdminController;
use App\Controllers\WorkOrderController;
use App\Controllers\AuditLogController;
use App\Controllers\SystemController;
use App\Controllers\TaskController;
return [


/*
|--------------------------------------------------------------------------
| Registrace nového tenantu
|--------------------------------------------------------------------------
*/
[
    'method' => 'GET',
    'path'   => '/register',
    'action' => [AuthController::class, 'registrationStepOne'],
    'auth'   => false,
],

[
    'method' => 'POST',
    'path'   => '/register',
    'action' => [AuthController::class, 'registrationStepOne'],
    'auth'   => false,
],

[
    'method' => 'GET',
    'path'   => '/register/check-email',
    'action' => [AuthController::class, 'registrationStepOneSucces'],
    'auth'   => false,
],

[
    'method' => 'GET',
    'path'   => '/register/complete',
    'action' => [AuthController::class, 'registrationStepTwo'],
    'auth'   => false,
],

[
    'method' => 'GET',
    'path'   => '/register/complete',
    'action' => [AuthController::class, 'registrationStepTwoSucces'],
    'auth'   => false,
],

[
    'method' => 'POST',
    'path'   => '/register/complete',
    'action' => [AuthController::class, 'registrationStepTwo'],
    'auth'   => false,
],


/*
|--------------------------------------------------------------------------
| ROOT + AUTH
|--------------------------------------------------------------------------
*/

[
    'method' => 'GET',
    'path'   => '/',
    'action' => [TaskController::class, 'index'],
    'auth'   => true,
],

[
    'method' => 'GET',
    'path'   => '/login',
    'action' => [AuthController::class, 'loginForm'],
    'auth'   => false,
    'title'  => 'Bó: Přihlášení',
],
[
    'method' => 'POST',
    'path'   => '/login',
    'action' => [AuthController::class, 'login'],
    'auth'   => false,
    'title'  => 'Bó: Přihlášení',
],


/*
|--------------------------------------------------------------------------
| TOKEN / AKTIVACE / RESET HESLA / bez Auth!
|--------------------------------------------------------------------------
*/

[
    'method' => 'GET',
    'path'   => '/activate',
    'action' => [AuthController::class, 'activate'],
    'auth'   => false,
    'title'  => 'Bó: Aktivace uživatele',
],

[
    'method' => 'POST',
    'path'   => '/activate',
    'action' => [AuthController::class, 'activatePost'],
    'auth'   => false,
    'title'  => 'Bó: Aktivace uživatele',

],

[
    'method' => 'GET',
    'path'   => '/reset-password',
    'action' => [AuthController::class, 'resetPassword'],
    'auth'   => false,
    'title'  => 'Bó: Reset hesla uživatele',

],

[
    'method' => 'POST',
    'path'   => '/reset-password',
    'action' => [AuthController::class, 'resetPasswordPost'],
    'auth'   => false,
    'title'  => 'Bó: Reset hesla uživatele',

],

[
    'method' => 'GET',
    'path'   => '/forgot-password',
    'action' => [AuthController::class, 'forgotPassword'],
    'auth'   => false,
    'title'  => 'Bó - Zapomenuté heslo',

],

[
    'method' => 'POST',
    'path'   => '/forgot-password',
    'action' => [AuthController::class, 'forgotPasswordPost'],
    'auth'   => false,
    'title'  => 'Bó: Zapomenuté heslo',

],

/*
|--------------------------------------------------------------------------
| DASHBOARD / ÚKOLY
|--------------------------------------------------------------------------
*/
/* Dashboard je prozatím zrušen
[
    'method'  => 'GET',
    'path'    => '/dashboard',
    'action'  => [DashboardController::class, 'index'],
    'auth'    => true,
    'roles'   => ['root', 'admin', 'mistr', 'predak', 'monter'],
    'menu'    => 'Úkoly',
    'section' => 'tasks',
    'title'   => 'Úkoly: Přehled',
],

*/
/*
|--------------------------------------------------------------------------
| TASKY
|--------------------------------------------------------------------------
*/
// výpis tasků
[
    'method'  => 'GET',
    'path'    => '/{tenant}/tasks',
    'action'  => [TaskController::class, 'index'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr', 'predak', 'monter'],
    'menu'    => 'Úkoly',
    'section' => 'tasks',
	'title'   => 'Úkoly: Přehled',
],
/*
[
    'method'  => 'GET',
    'path'    => '/{tenant}/tasks',
    'action'  => [TaskController::class, 'index'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr', 'predak', 'monter'],
    'submenu'    => 'Přehled',
    'section' => 'tasks',
	'title'   => 'Úkoly > Přehled',
],
*/

//přidání reportu
[
    'method'  => 'GET',
    'path'    => '/{tenant}/tasks/{taskId}/report',
    'action'  => [TaskController::class, 'addTaskReportGet'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr', 'predak', 'monter'],
    'section' => 'tasks',
	'title'   => 'Úkoly: Přidat report',
],

[
    'method'  => 'POST',
    'path'    => '/{tenant}/tasks/{taskId}/report',
    'action'  => [TaskController::class, 'addTaskReportPost'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr', 'predak', 'monter'],
    'section' => 'tasks',
	'title'   => 'Úkoly: Přidat report',
],

//vytvoření samotného tasku při zakázce
[
    'method' => 'POST',
    'path'   => '/{tenant}/tasks/create',
    'action' => [TaskController::class, 'create'],
    'auth'   => true,
	'roles'   => ['admin', 'mistr'],
	'title'   => 'Úkoly: Přidat úkol',
],

//úprava tasku:
[
    'method' => 'GET',
    'path'   => '/{tenant}/tasks/{taskId}/edit',
    'action' => [WorkOrderController::class, 'detailOrderEditTask'],
    'auth'   => true,
	'roles'   => ['admin', 'mistr'],
	'title'   => 'Úkoly: upravit úkol',
],
[
    'method' => 'POST',
    'path'   => '/{tenant}/tasks/{taskId}/edit',
    'action' => [WorkOrderController::class, 'detailOrderEditTask'],
    'auth'   => true,
    'roles'   => ['admin', 'mistr'],
	'title'   => 'Úkoly: upravit úkol',
],


[
    'method' => 'POST',
    'path'   => '/{tenant}/tasks/{taskId:\d+}/done',
    'action' => [TaskController::class, 'done'],
    'auth'   => true,
],

[
    'method' => 'POST',
    'path'   => '/{tenant}/tasks/{taskId:\d+}/cancel',
    'action' => [TaskController::class, 'cancel'],
    'auth'   => true,
],

/*
|--------------------------------------------------------------------------
| ZAKÁZKY, index (výpis zakázek)
|--------------------------------------------------------------------------
*/

[
    'method'  => 'GET',
    'path'    => '/{tenant}/work-orders',
    'action'  => [WorkOrderController::class, 'index'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr', 'predak'],
    'menu'    => 'Zakázky',
    'submenu' => 'Přehled',
    'section' => 'workorders',
    'title'   => 'Zakázky: Přehled',
],


/*
|--------------------------------------------------------------------------
| ZAKÁZKY, vytvoření zakázky: create
|--------------------------------------------------------------------------
*/

[
    'method'  => 'GET',
    'path'    => '/{tenant}/work-orders/create',
    'action'  => [WorkOrderController::class, 'createForm'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr', 'predak'],
    'submenu' => 'Nová zakázka',
    'section' => 'workorders',
    'title'   => 'Zakázky: Nová',
],

[
    'method' => 'POST',
    'path'   => '/{tenant}/work-orders/create',
    'action' => [WorkOrderController::class, 'create'],
    'auth'   => true,
    'roles'  => ['admin', 'mistr', 'predak'],
],

/*
|--------------------------------------------------------------------------
| ZAKÁZKY, Detail zakázky
|--------------------------------------------------------------------------
*/

[
    'method'  => 'GET',
    'path'    => '/{tenant}/work-orders/{orderId:\d+}/detail',
    'action'  => [WorkOrderController::class, 'detailOrder'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr', 'predak'],
    'section' => 'workorders',
    'title'   => 'Zakázky: Detail',
],


/*
|--------------------------------------------------------------------------
|  úprava zakázky
|--------------------------------------------------------------------------
*/ 
[
    'method'  => 'GET',
    'path'    => '/{tenant}/work-orders/{orderId:\d+}/edit',
    'action'  => [WorkOrderController::class, 'edit'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr', 'predak'],
    'section' => 'workorders',
    'title'   => 'Zakázky: Upravit',
],
[
    'method'  => 'POST',
    'path'    => '/{tenant}/work-orders/{orderId:\d+}/edit',
    'action'  => [WorkOrderController::class, 'update'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr', 'predak'],
],

/*
|--------------------------------------------------------------------------
|  routy pro zavření Zakázky (done, canceled)
|--------------------------------------------------------------------------
*/
[
    'method'  => 'POST',
    'path'    => '/{tenant}/work-orders/{orderId:\d+}/close/canceled',
    'action'  => [WorkOrderController::class, 'closeOrderCanceled'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
],

[
    'method'  => 'POST',
    'path'    => '/{tenant}/work-orders/{orderId:\d+}/close/done',
    'action'  => [WorkOrderController::class, 'closeOrderDone'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
],

/*
|--------------------------------------------------------------------------
|  routy pro zavření tasku k zakázce (done, canceled) Ještě možná budeme rušit/přesouvat
|--------------------------------------------------------------------------
*/

[
    'method'  => 'POST',
    'path'    => '/{tenant}/work-orders/{orderId:\d+}/tasks/{taskId:\d+}/done',
    'action'  => [WorkOrderController::class, 'closeTaskDone'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
],

[
    'method'  => 'POST',
    'path'    => '/{tenant}/work-orders/{orderId:\d+}/tasks/{taskId:\d+}/cancel',
    'action'  => [WorkOrderController::class, 'closeTaskCanceled'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
],


/*
|--------------------------------------------------------------------------
| TÝMY
|--------------------------------------------------------------------------
*/

[
    'method'  => 'GET',
    'path'    => '/{tenant}/teams',
    'action'  => [TeamController::class, 'index'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'menu'    => 'Týmy',
    'submenu' => 'Aktivní',
    'section' => 'teams',
    'title'   => 'Týmy: Aktivní',
],

[
    'method'  => 'GET',
    'path'    => '/{tenant}/teams/inactive',
    'action'  => [TeamController::class, 'inactive'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'submenu' => 'Deaktivované',
    'section' => 'teams',
    'title'   => 'Týmy: Deaktivované',
],

[
    'method'  => 'GET',
    'path'    => '/{tenant}/teams/create',
    'action'  => [TeamController::class, 'create'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'submenu' => 'Vytvořit tým',
    'section' => 'teams',
    'title'   => 'Týmy: Nový',
],

[
    'method'  => 'POST',
    'path'    => '/{tenant}/teams/create',
    'action'  => [TeamController::class, 'store'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'teams',
    'title'   => 'Týmy: Nový',
],

[
    'method'  => 'GET',
    'path'    => '/{tenant}/teams/{id:\d+}/edit',
    'action'  => [TeamController::class, 'edit'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],    
    'section' => 'teams',
    'title'   => 'Týmy: Upravit tým',
],

[
    'method'  => 'POST',
    'path'    => '/{tenant}/teams/{id:\d+}/edit',
    'action'  => [TeamController::class, 'update'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],

    'section' => 'teams',
    'title'   => 'Týmy: Upravit tým',
],


[
    'method'  => 'GET',
    'path'    => '/{tenant}/teams/toggle/{id:\d+}',
    'action'  => [TeamController::class, 'toggle'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'teams',
],



/*
|--------------------------------------------------------------------------
| UŽIVATELÉ
|--------------------------------------------------------------------------
*/

[
    'method'  => 'GET',
    'path'    => '/{tenant}/users',
    'action'  => [UserController::class, 'index'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'menu'    => 'Uživatelé',
    'submenu' => 'Přehled',
    'section' => 'users',
    'title'   => 'Uživatelé: Přehled',
],

[
    'method'  => 'GET',
    'path'    => '/{tenant}/users/{id:\d+}/detail',
    'action'  => [UserController::class, 'userDetail'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'users',
    'title'   => 'Uživatelé: Detail',
],

[
    'method'  => 'POST',
    'path'    => '/{tenant}/users/{id:\d+}/send-reset-password',
    'action'  => [UserController::class, 'sendResetPassword'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
],

[
    'method'  => 'POST',
    'path'    => '/{tenant}/users/{id:\d+}/resend-activation',
    'action'  => [UserController::class, 'resendActivationEmail'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
],


[
    'method'  => 'GET',
    'path'    => '/{tenant}/users/create',
    'action'  => [UserController::class, 'create'],
    'auth'    => true,
    'roles'   => ['admin'],
    'submenu' => 'Přidat uživatele',
    'section' => 'users',
    'title'   => 'Uživatelé: Vytvoření nového uživatele ',
],

[
    'method' => 'POST',
    'path'   => '/{tenant}/users/create',
    'action' => [UserController::class, 'store'],
    'auth'   => true,
    'roles'  => ['admin'],
],

[
    'method' => 'GET',
    'path'   => '/{tenant}/users/{id:\d+}/edit',
    'action' => [UserController::class, 'edit'],
    'auth'   => true,
    'roles'  => ['admin', 'mistr'],
    'section'=> 'users',
],

[
    'method' => 'POST',
    'path'   => '/{tenant}/users/{id:\d+}/edit',
    'action' => [UserController::class, 'update'],
    'auth'   => true,
    'roles'  => ['admin', 'mistr'],
],
/*-------------------------------------
Přepínaní rolí u admina
-------------------------------------**/
[
    'method'        => 'GET',
    'path'          => '/{tenant}/admin/switch-role/{role:admin|mistr|predak|monter}',
    'action'        => [AdminController::class, 'switchRole'],
    'global_roles'  => ['admin'],
    'auth'          => true,
],



/*
|--------------------------------------------------------------------------
| ADMIN / SYSTEM
|--------------------------------------------------------------------------
*/

[
    'method' => 'GET',
    'path'   => '/{tenant}/system',
    'action' => [SystemController::class, 'index'],
    'roles'  => ['root'],
    'menu'   => 'Administrace',
    'submenu'=> 'Systém',
    'section'=> 'system',
    'auth'   => true,
],

[
    'method' => 'GET',
    'path'   => '/{tenant}/admin/audit',
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
    'path'   => '/{tenant}/logout',
    'action' => [AuthController::class, 'logout'],
    'auth'   => true,
    'menu'   => 'Odhlásit',
],

];
