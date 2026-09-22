<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

//use App\Core\Csrf;
use App\Core\Url;
//use App\Services\Invoice\InvoiceService;

require __DIR__ . '/../layout/header.php';

$invoices   = $view->invoices;

$errors    = $view->errors;
?>


<?php if ($invoices === []) : ?>

    <p>Ještě nemáte vytvořené žádné faktury.</p>

    <?php if ($view->invoiceSettingsConfirmed === false) : ?>

        <p>
            Před vytvořením první faktury je <a href="<?= Url::to('/{tenant}/system/settings/billing/#main') ?>">potřeba nastavit
            počáteční číslo faktur</a>. 
        </p>

    <?php endif; ?>

<?php else : ?>

    <?php dc($invoices); ?>

<?php endif; ?>



<?php require __DIR__ . '/../layout/footer.php';