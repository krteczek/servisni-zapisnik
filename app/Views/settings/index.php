<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

require __DIR__ . '/../layout/header.php';

$data = $view->data;
?>

<div class="settings-container" id="main">

    <!-- MAIN TABS -->
    <div class="settings-tabs">

        <button class="main-tab-button"
                data-target="settings-work-orders">
            Zakázky
        </button>

        <button class="main-tab-button"
                data-target="settings-billing">
            Fakturace
        </button>

        <button class="main-tab-button"
                data-target="settings-exports">
            Exporty
        </button>

        <button class="main-tab-button"
                data-target="settings-reports">
            Výkazy
        </button>

        <button class="main-tab-button"
                data-target="settings-company">
            Firma
        </button>

    </div>

    <!-- ===================================================== -->
    <!-- ZAKÁZKY -->
    <!-- ===================================================== -->

    <div id="settings-work-orders"
         class="main-tab-content">

        <?php 
            //require __DIR__ . '/partials/_work_orders.php'; 
        ?>

    </div>

    <!-- ===================================================== -->
    <!-- FAKTURACE -->
    <!-- ===================================================== -->

    <div id="settings-billing"
         class="main-tab-content"
         hidden>

        <?php 
            require __DIR__ . '/partials/_billing.php'; 
        ?>

    </div>

    <!-- ===================================================== -->
    <!-- EXPORTY -->
    <!-- ===================================================== -->

    <div id="settings-exports"
         class="main-tab-content"
         hidden>

        <?php 
            // require __DIR__ . '/partials/_exports.php'; 
        ?>

    </div>

    <!-- ===================================================== -->
    <!-- VÝKAZY -->
    <!-- ===================================================== -->

    <div id="settings-reports"
         class="main-tab-content"
         hidden>

        <?php 
            // require __DIR__ . '/partials/_reports.php'; 
        ?>

    </div>

    <!-- ===================================================== -->
    <!-- FIRMA -->
    <!-- ===================================================== -->

    <div id="settings-company"
         class="main-tab-content"
         hidden>

        <?php 
            //require __DIR__ . '/partials/_company.php'; 
        ?>

    </div>

</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>