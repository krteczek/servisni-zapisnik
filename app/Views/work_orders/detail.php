<?php
declare(strict_types=1);
$css = '';
require __DIR__ . '/style.php';
require __DIR__ . '/../layout/header.php';

use App\Core\Url;
use App\Core\Csrf;
use App\Core\Access;
/** @var array $order */
$workOrder = $this->view->order;

$canCloseDone = $workOrder['canCloseDone'];
$canCloseCanceled = $workOrder['canCloseCanceled'];

$order = $view->order;
?>
<?= $css ?>

<div class="wo-detail">

    <!-- HEADER -->
    <div class="wo-detail-header">
        <div class="wo-detail-title">
            Zakázka #<?= (int) $order['id'] ?> – <?= htmlspecialchars($order['title'] ?? '') ?>
        </div>

        <div class="wo-detail-meta">
            <span class="wo-status status-open" title="Status zakázky">Otevřená</span>
            <span class="wo-priority priority-medium" title="Priorita zakázky">Střední</span>
        </div>
    </div>

    <!-- BASIC INFO -->
<div class="wo-section">
    <h3>Základní informace</h3>

    <div class="wo-info-main">
        <strong>Popis: </strong><br>
        <?= nl2br(htmlspecialchars($order['description'] ?: 'Nezadán')) ?>
    </div>

    <div class="wo-info-grid">
        <div class="wo-info-box">
            <span class="label">Zdroj: </span>
            <span class="value">
                <?= htmlspecialchars($workOrder['source'] ?: 'Nezadán') ?>
            </span>
        </div>

        <div class="wo-info-box">
            <span class="label">Požadavek vznesl: </span>
            <span class="value">
                <?= htmlspecialchars($workOrder['requested_by'] ?: 'Nezadán') ?>
            </span>
        </div>

        <div class="wo-info-box">
            <span class="label">Kontakt: </span>
            <span class="value">
                <?= htmlspecialchars($workOrder['contact'] ?: 'Nezadán') ?>
            </span>
        </div>
    </div>
</div>

<!-- Přidání tasku k zakázce -->
<div class="wo-section">
<h3>Přidání úkolu</h3>
	<p>Tady bych to viděl na přidávání úkolů k zakázce. </p>
	<p>a někde pod tím i jejich zobrazení včetně plnění (task assigments) nebo je to blbost? Kam potom dát ty dbě tlačítka pro ukončení nebo storno?</p>
</div>


    <!-- ACTIONS -->
    <div class="wo-section">
        <h3>Akce nad zakázkou</h3>

        <div class="wo-actions-detail">
<?php if($canCloseCanceled === true) : ?>

            <!-- STORNO -->
            <div class="wo-action-box">
                <p>
                    V případě, že zakázka nemá žádné úkoly nebo má pouze úkoly stornované,
                    je možné zakázku zrušit.
                </p>

                <form method="post"
                      action="<?= Url::to('/work-orders/' . $order['id'] . '/close/canceled') ?>"
                      onsubmit="return confirm('Opravdu chcete zakázku zrušit? Tento krok je nevratný.');">
                    <?= Csrf::getField() ?>
                    <button class="btn btn-danger">
                      ⚠  Zrušit (stornovat) zakázku
                    </button>
                </form>
            </div>
<?php elseif($canCloseDone === true) : ?>
            <!-- UKONČENÍ -->
            <div class="wo-action-box">
                <p>
                    Pokud má zakázka všechny úkoly uzavřené (hotové),
                    je možné ji definitivně ukončit.
                </p>

                <form method="post"
                      action="<?= Url::to('/work-orders/' . $order['id'] . '/close/done') ?>"
                      onsubmit="return confirm('Opravdu chcete zakázku ukončit? Tento krok je nevratný.');">
                    <?= Csrf::getField() ?>
                    <button class="btn btn-success">
                       ⚠ Ukončit zakázku
                    </button>
                </form>
            </div>
<?php else : ?>
<div class="wo-action-box">
	<p>Momentálně není žádná akce dostupná.</p>
</div>
<?php endif;?>
        </div>
    </div>
<p>
    <a href="<?= Url::to('/work-orders') ?>/#main" class="btn btn-secondary">
        ← Zpět na přehled
    </a>
</p>

</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>