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
use App\Controllers\PageController;
use App\Controllers\TaskRecurringController;
use App\Controllers\BillingExportController;
use App\Controllers\ContactsController;
use App\Controllers\ExportsController;
use App\Controllers\ArchiveController;
use App\Controllers\WorkbenchController;

return [

/*
|--------------------------------------------------------------------------
| Veřejné stránky (cookies, podmínky použití...
|--------------------------------------------------------------------------
*/

[
    'method' => 'GET',
    'path'   => '/pages/terms',
    'action' => [PageController::class, 'terms'],
    'auth'   => false,
    'title'  => 'Obchodní podmínky',
],
[
    'method' => 'GET',
    'path'   => '/pages/privacy',
    'action' => [PageController::class, 'privacy'],
    'auth'   => false,
    'title'  => 'Ochrana osobních údajů',
],
[
    'method' => 'GET',
    'path'   => '/pages/cookies',
    'action' => [PageController::class, 'cookies'],
    'auth'   => false,
    'title'  => 'Zásady používání cookies',
],


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
    'title'  => 'Registrace firmy',
    'menu'    => 'Registrace firmy',
    'section' => 'register',
],

[
    'method' => 'POST',
    'path'   => '/register',
    'action' => [AuthController::class, 'registrationStepOne'],
    'auth'   => false,
    'title'  => 'Registrace firmy',

],

[
    'method' => 'GET',
    'path'   => '/register/check-email',
    'action' => [AuthController::class, 'registrationStepOneSucces'],
    'auth'   => false,
    'title'  => 'Registrace firmy',
],

[
    'method' => 'GET',
    'path'   => '/register/complete',
    'action' => [AuthController::class, 'registrationStepTwo'],
    'auth'   => false,
    'title'  => 'Dokončení registrace firmy',
],
[
    'method' => 'POST',
    'path'   => '/register/complete',
    'action' => [AuthController::class, 'registrationStepTwo'],
    'auth'   => false,
    'title'  => 'Dokončení registrace firmy',
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
    'title'  => 'Přihlášení',
    'menu'    => 'Přihlášení',
    'section' => 'login',

],
[
    'method' => 'POST',
    'path'   => '/login',
    'action' => [AuthController::class, 'login'],
    'auth'   => false,
    'title'  => 'Přihlášení',
],


/*
|--------------------------------------------------------------------------
| TOKEN / AKTIVACE / RESET HESLA / bez Auth!
|--------------------------------------------------------------------------
*/

[
    'method' => 'GET',
    'path'   => '/activate/complete',
    'action' => [AuthController::class, 'activateGet'],
    'auth'   => false,
    'title'  => 'Aktivace uživatele',
],

[
    'method' => 'POST',
    'path'   => '/activate/complete',
    'action' => [AuthController::class, 'activatePost'],
    'auth'   => false,
    'title'  => 'Aktivace uživatele',

],

[
    'method' => 'GET',
    'path'   => '/reset-password',
    'action' => [AuthController::class, 'resetPasswordGet'],
    'auth'   => false,
    'title'  => 'Reset hesla uživatele',

],

[
    'method' => 'POST',
    'path'   => '/reset-password',
    'action' => [AuthController::class, 'resetPasswordPost'],
    'auth'   => false,
    'title'  => 'Reset hesla uživatele',

],

[
    'method' => 'GET',
    'path'   => '/forgot-password',
    'action' => [AuthController::class, 'forgotPassword'],
    'auth'   => false,
    'title'  => 'Zapomenuté heslo',

],

[
    'method' => 'POST',
    'path'   => '/forgot-password',
    'action' => [AuthController::class, 'forgotPasswordPost'],
    'auth'   => false,
    'title'  => 'Zapomenuté heslo',

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
| Workbench - pracovní stůl pro rychlý přehled o úkolech a zakázkách
|--------------------------------------------------------------------------
*/
[ 
    'method' => 'GET', 
    'path' => '/workbench', 
    'action' => [WorkbenchController::class, 'index'], 
    'auth' => true, 
    'roles' => ['admin', 'mistr'], 
    'menu' => 'Pracovní stůl', 
    'section' => 'workbench', 
    'title' => 'Pracovní stůl', ],
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
    'path'    => '/{tenant}/tasks/{taskId:\d+}/report',
    'action'  => [TaskController::class, 'addTaskReportGet'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr', 'predak', 'monter'],
    'section' => 'tasks',
	'title'   => 'Úkoly: Přidat report',
],

[
    'method'  => 'POST',
    'path'    => '/{tenant}/tasks/{taskId:\d+}/report',
    'action'  => [TaskController::class, 'addTaskReportPost'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr', 'predak', 'monter'],
    'section' => 'tasks',
	'title'   => 'Úkoly: Přidat report',
],

//vytvoření samotného tasku při zakázce
//vytvoření samotného tasku při zakázce
[
    'method' => 'GET',
    'path'   => '/{tenant}/work-orders/{orderId:\d+}/tasks/create',
    'action' => [TaskController::class, 'createFormGet'],
    'auth'   => true,
	'roles'   => ['admin', 'mistr'],
	'title'   => 'Úkoly: Přidat úkol',
],

[
    'method' => 'POST',
    'path'   => '/{tenant}/work-orders/{orderId:\d+}/tasks/create',
    'action' => [TaskController::class, 'createFormPost'],
    'auth'   => true,
	'roles'   => ['admin', 'mistr'],
	'title'   => 'Úkoly: Přidat úkol',
],


// vytvoření tasku ze zakázky
[
    'method' => 'GET',
    'path'   => '/{tenant}/work-orders/{orderId:\d+}/tasks/createFromOrder',
    'action' => [TaskController::class, 'createTaskFromOrderGet'],
    'auth'   => true,
	'roles'   => ['admin', 'mistr'],
	'title'   => 'Úkoly: Přidat úkol',
],

[
    'method' => 'POST',
    'path'   => '/{tenant}/work-orders/{orderId:\d+}/tasks/createFromOrder',
    'action' => [TaskController::class, 'createTaskFromOrderPost'],
    'auth'   => true,
	'roles'   => ['admin', 'mistr'],
	'title'   => 'Úkoly: Přidat úkol',
],
//úprava tasku:
[
    'method' => 'GET',
    'path'   => '/{tenant}/tasks/{taskId:\d+}/edit',
    'action' => [TaskController::class, 'editTaskGet'],
    'auth'   => true,
	'roles'   => ['admin', 'mistr'],
	'title'   => 'Úkoly: upravit úkol',
],
[
    'method' => 'POST',
    'path'   => '/{tenant}/tasks/{taskId:\d+}/edit',
    'action' => [TaskController::class, 'editTaskPost'],
    'auth'   => true,
    'roles'   => ['admin', 'mistr'],
	'title'   => 'Úkoly: upravit úkol',
],

[
    'method' => 'GET',
    'path'   => '/{tenant}/tasks/{taskId:\d+}/clone',
    'action' => [TaskController::class, 'cloneTaskGet'],
    'auth'   => true,
	'roles'   => ['admin', 'mistr'],
	'title'   => 'Úkoly: Vytvořit klon úkolu',
],
[
    'method' => 'POST',
    'path'   => '/{tenant}/tasks/{taskId:\d+}/clone',
    'action' => [TaskController::class, 'cloneTaskPost'],
    'auth'   => true,
    'roles'   => ['admin', 'mistr'],
	'title'   => 'Úkoly: Vytvořit klon úkolu',
],

/*
|--------------------------------------------------------------------------
| RECURRING Tasks create a edit
|--------------------------------------------------------------------------
*/
[
    'method' => 'GET',
    'path'   => '/{tenant}/tasks/{taskId:\d+}/recurring',
    'action' => [TaskController::class, 'recurringGet'],
    'auth'   => true,
	'roles'   => ['admin', 'mistr'],
	'title'   => 'Úkoly: Vytvořit šablonu opakujícího se úkolu',
],

[
    'method' => 'POST',
    'path'   => '/{tenant}/tasks/{taskId:\d+}/recurring',
    'action' => [TaskController::class, 'recurringPost'],
    'auth'   => true,
    'roles'   => ['admin', 'mistr'],
	'title'   => 'Úkoly: Vytvořit šablonu opakujícího se úkolu',
],
[
    'method' => 'GET',
    'path'   => '/{tenant}/tasks/{taskId:\d+}/recurringEdit',
    'action' => [TaskController::class, 'recurringGet'],
    'auth'   => true,
	'roles'   => ['admin', 'mistr'],
	'title'   => 'Úkoly: Vytvořit šablonu opakujícího se úkolu',
],

[
    'method' => 'POST',
    'path'   => '/{tenant}/tasks/{taskId:\d+}/recurringEdit',
    'action' => [TaskController::class, 'recurringPost'],
    'auth'   => true,
    'roles'   => ['admin', 'mistr'],
	'title'   => 'Úkoly: Vytvořit šablonu opakujícího se úkolu',
],


[
    'method' => 'POST',
    'path'   => '/{tenant}/tasks/{taskId:\d+}/done',
    'action' => [TaskController::class, 'done'],
    'roles'   => ['admin', 'mistr'],
    'auth'   => true,
],

[
    'method' => 'POST',
    'path'   => '/{tenant}/tasks/{taskId:\d+}/recurring-done',
    'action' => [TaskController::class, 'doneRecurring'],
    'roles'   => ['admin', 'mistr'],
    'auth'   => true,
],

[
    'method' => 'POST',
    'path'   => '/{tenant}/tasks/{taskId:\d+}/cancel',
    'action' => [TaskController::class, 'cancel'],
    'roles'   => ['admin', 'mistr'],
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
    'action' => [WorkOrderController::class, 'createFormStore'],
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
    'action'  => [WorkOrderController::class, 'editForm'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr', 'predak'],
    'section' => 'workorders',
    'title'   => 'Zakázky: Upravit',
],
[
    'method'  => 'POST',
    'path'    => '/{tenant}/work-orders/{orderId:\d+}/edit',
    'action'  => [WorkOrderController::class, 'editFormUpdate'],
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
* /

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
*/

/*
|--------------------------------------------------------------------------
|  Archiv
|--------------------------------------------------------------------------
*/
[
    'method'  => 'GET',
    'path'    => '/{tenant}/archive',
    'action'  => [ArchiveController::class, 'index'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
 
],

[
    'method'  => 'GET',
    'path'    => '/{tenant}/archive/tasks',
    'action'  => [ArchiveController::class, 'tasks'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'menu'    => 'Archiv',
    'submenu' => 'Úkoly',
    'section' => 'archive',
    'title'   => 'Archiv: Úkoly',

],
[
    'method'  => 'GET',
    'path'    => '/{tenant}/archive/work-orders',
    'action'  => [ArchiveController::class, 'workOrders'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'menu'    => 'Archiv',
    'submenu' => 'Zakázky',
    'section' => 'archive',
    'title'   => 'Archiv: Zakázky',

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


/* ----------------------------------------
   Zákazníci
-------------------------------------------*/
[
    'method'  => 'GET',
    'path'    => '/{tenant}/contacts/index',
    'action'  => [ContactsController::class, 'index'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'contacts',
    'menu'    => 'Zákazníci',
    'submenu' => 'Výpis zákazníků',
    'title'   => 'Zákazníci: Výpis zákazníků',
],

[
    'method' => 'GET',
    'path'   => '/{tenant}/contacts/create',
    'action' => [ContactsController::class, 'createContact'],
    'auth'   => true,
    'roles'  => ['admin', 'mistr'],
    'section'=> 'contacts',
    'menu'   => 'Zákazníci',
    'submenu'=> 'Vytvořit zákazníka',
    'title'  => 'Zákazníci: Vytvořit zákazníka',

],

[
    'method' => 'POST',
    'path'   => '/{tenant}/contacts/create',
    'action' => [ContactsController::class, 'storeContact'],
    'auth'   => true,
    'roles'  => ['admin', 'mistr'],
    'section'=> 'contacts',
    'title'  => 'Zákazníci: Vytvořit zákazníka',

],

[
    'method' => 'GET',
    'path'   => '/{tenant}/contacts/{id:\d+}/edit',
    'action' => [ContactsController::class, 'editContact'],
    'auth'   => true,
    'roles'  => ['admin', 'mistr'],
    'section'=> 'contacts',
    'title'  => 'Zákazníci: Upravit zákazníka',
],

[
    'method' => 'POST',
    'path'   => '/{tenant}/contacts/{id:\d+}/edit',
    'action' => [ContactsController::class, 'updateContact'],
    'auth'   => true,
    'roles'  => ['admin', 'mistr'],
    'section'=> 'contacts',
    'title'  => 'Zákazníci: Upravit zákazníka',
],



/*-------------------------------------
   Fakturace
-------------------------------------** /
[
    'method'  => 'GET',
    'path'    => '/{tenant}/exports/billing/index',
    'action'  => [BillingExportController::class, 'index'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'exports',
    'menu'   => 'Exporty',
    'submenu'=> 'Výpis exportů',
    'title'   => 'Exporty: Výpis exportů',
],

[
    'method'  => 'GET',
    'path'    => '/{tenant}/exports/billing/create',
    'action'  => [BillingExportController::class, 'create'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'exports',
    'menu'   => 'Exporty',
    'submenu'=> 'Výpis fakturačních exportů',
    'title'   => 'Exporty: Vytvořit fakturační export',
],

[
    'method'  => 'POST',
    'path'    => '/{tenant}/exports/billing/create',
    'action'  => [BillingExportController::class, 'store'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'exports',
    'menu'   => 'Exporty',
    'submenu'=> 'Výpis fakturačních exportů',
    'title'   => 'Exporty: Vytvořit fakturační export',
],

[
    'method'  => 'GET',
    'path'    => '/{tenant}/exports/billing/{id}/detail',
    'action'  => [BillingExportController::class, 'detail'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'exports',
    'menu'    => 'Exporty',
    'title'   => 'Exporty: Detail fakturačního exportu',
],

[
    'method'  => 'GET',
    'path'    => '/{tenant}/exports/billing/{id}/pdf',
    'action'  => [BillingExportController::class, 'pdf'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'menu'    => 'Exporty',
    'section' => 'exports',
    'title'   => 'Exporty: Export do PDF',
],

/*-------------------------------------
   Exporty
-------------------------------------** /
[
    'method'  => 'GET',
    'path'    => '/{tenant}/exports/data/tasks',
    'action'  => [ExportsController::class, 'exportTasksGet'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'exports',
    'menu'   => 'Exporty',
    'submenu'=> 'Výpis datových exportů',
    'title'   => 'Exporty: Výpis datových exportů',
],

[
    'method'  => 'POST',
    'path'    => '/{tenant}/exports/data/tasks',
    'action'  => [ExportsController::class, 'exportTasksPost'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
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
    'path'   => '/{tenant}/system/companies/{id}',
    'action' => [SystemController::class, 'companyDetail'],
    'roles'  => ['root'],
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
    'path'   => '/logout',
    'action' => [AuthController::class, 'logout'],
    'auth'   => true,
    'menu'   => 'Odhlásit',
],

];
