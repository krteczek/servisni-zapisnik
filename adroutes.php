// ==================================================
// FAKTURY – SPOLEČNÝ PŘEHLED
// ==================================================

[
    'pattern' => '/{tenant}/billing/invoice',
    'method'  => 'GET',
    'handler' => [InvoiceController::class, 'index'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'billing',
    'menu'    => 'Faktury',
],


// ==================================================
// NÁVRHY FAKTUR
// ==================================================

// Vytvoření návrhu z úkolu
[
    'pattern' => '/{tenant}/billing/invoice/draft/create/from-task/{id:\d+}',
    'method'  => 'GET',
    'handler' => [InvoiceDraftController::class, 'createFromTask'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'billing',
    'menu'    => 'Faktury',
],

[
    'pattern' => '/{tenant}/billing/invoice/draft/create/from-task/{id:\d+}',
    'method'  => 'POST',
    'handler' => [InvoiceDraftController::class, 'storeFromTask'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'billing',
    'menu'    => 'Faktury',
],

// Vytvoření návrhu ze zakázky
[
    'pattern' => '/{tenant}/billing/invoice/draft/create/from-work-order/{id:\d+}',
    'method'  => 'GET',
    'handler' => [InvoiceDraftController::class, 'createWorkOrder'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'billing',
    'menu'    => 'Faktury',
],

[
    'pattern' => '/{tenant}/billing/invoice/draft/create/from-work-order/{id:\d+}',
    'method'  => 'POST',
    'handler' => [InvoiceDraftController::class, 'storeWorkOrder'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'billing',
    'menu'    => 'Faktury',
],

// Detail návrhu
[
    'pattern' => '/{tenant}/billing/invoice/draft/{id:\d+}/detail',
    'method'  => 'GET',
    'handler' => [InvoiceDraftController::class, 'detail'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'billing',
    'menu'    => 'Faktury',
],

// Editace návrhu
[
    'pattern' => '/{tenant}/billing/invoice/draft/{id:\d+}/edit',
    'method'  => 'GET',
    'handler' => [InvoiceDraftController::class, 'edit'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'billing',
    'menu'    => 'Faktury',
],

[
    'pattern' => '/{tenant}/billing/invoice/draft/{id:\d+}/update',
    'method'  => 'POST',
    'handler' => [InvoiceDraftController::class, 'update'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'billing',
    'menu'    => 'Faktury',
],

// Zahazování návrhu
[
    'pattern' => '/{tenant}/billing/invoice/draft/{id:\d+}/discard',
    'method'  => 'POST',
    'handler' => [InvoiceDraftController::class, 'discard'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'billing',
    'menu'    => 'Faktury',
],

// Vystavení návrhu
[
    'pattern' => '/{tenant}/billing/invoice/draft/{id:\d+}/issue',
    'method'  => 'POST',
    'handler' => [InvoiceDraftController::class, 'issue'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'billing',
    'menu'    => 'Faktury',
],


// ==================================================
// ZAHOZENÉ NÁVRHY
// ==================================================

// Seznam zahodených návrhů
[
    'pattern' => '/{tenant}/billing/invoice/deleted',
    'method'  => 'GET',
    'handler' => [InvoiceDraftController::class, 'deleted'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'billing',
    'menu'    => 'Faktury',
],

// Detail zahodeného návrhu
[
    'pattern' => '/{tenant}/billing/invoice/deleted/{id:\d+}/detail',
    'method'  => 'GET',
    'handler' => [InvoiceDraftController::class, 'deletedDetail'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'billing',
    'menu'    => 'Faktury',
],

// Obnovení návrhu
[
    'pattern' => '/{tenant}/billing/invoice/deleted/{id:\d+}/restore',
    'method'  => 'POST',
    'handler' => [InvoiceDraftController::class, 'restore'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'billing',
    'menu'    => 'Faktury',
],


// ==================================================
// VYDANÉ FAKTURY
// ==================================================

// Detail vydané faktury
[
    'pattern' => '/{tenant}/billing/invoice/issued/{id:\d+}/detail',
    'method'  => 'GET',
    'handler' => [InvoiceController::class, 'detail'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'billing',
    'menu'    => 'Faktury',
],

// PDF vydané faktury
[
    'pattern' => '/{tenant}/billing/invoice/issued/{id:\d+}/pdf',
    'method'  => 'GET',
    'handler' => [InvoiceController::class, 'pdf'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'billing',
    'menu'    => 'Faktury',
],

// Storno vydané faktury
[
    'pattern' => '/{tenant}/billing/invoice/issued/{id:\d+}/cancel',
    'method'  => 'POST',
    'handler' => [InvoiceController::class, 'cancel'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'billing',
    'menu'    => 'Faktury',
],


// ==================================================
// ZAPLACENÉ FAKTURY
// ==================================================

// Detail zaplacené faktury
[
    'pattern' => '/{tenant}/billing/invoice/paid/{id:\d+}/detail',
    'method'  => 'GET',
    'handler' => [InvoiceController::class, 'detail'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'billing',
    'menu'    => 'Faktury',
],

// PDF zaplacené faktury
[
    'pattern' => '/{tenant}/billing/invoice/paid/{id:\d+}/pdf',
    'method'  => 'GET',
    'handler' => [InvoiceController::class, 'pdf'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'billing',
    'menu'    => 'Faktury',
],


// ==================================================
// STORNOVANÉ FAKTURY
// ==================================================

// Detail stornované faktury
[
    'pattern' => '/{tenant}/billing/invoice/cancelled/{id:\d+}/detail',
    'method'  => 'GET',
    'handler' => [InvoiceController::class, 'detail'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'billing',
    'menu'    => 'Faktury',
],

// PDF stornované faktury
[
    'pattern' => '/{tenant}/billing/invoice/cancelled/{id:\d+}/pdf',
    'method'  => 'GET',
    'handler' => [InvoiceController::class, 'pdf'],
    'auth'    => true,
    'roles'   => ['admin', 'mistr'],
    'section' => 'billing',
    'menu'    => 'Faktury',
],