<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

//use App\Core\Csrf;
use App\Core\Url;
//use App\Services\Invoice\InvoiceService;

require __DIR__ . '/../layout/header.php';

$invoice   = $view->invoice;

$errors    = $view->errors;
//dc($invoice, $errors);
?>

<?php require __DIR__ . '/../layout/footer.php'; ?>