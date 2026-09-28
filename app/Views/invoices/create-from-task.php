<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Csrf;
use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$invoice   = $view->data['invoice'] ?? [];
$workOrder = $view->data['workOrder'] ?? [];
$customer  = $view->data['customer'] ?? [];
$items     = $view->data['items'] ?? [];
$errors    = $view->errors;

?>

<div class="create-container-invoice">

    <div class="card-invoice">

        <div class="card-header">
            Vytvoření faktury z úkolu
        </div>

        <div class="card-body">

            <?php require __DIR__ . '/../layout/formsErrors.php'; ?>

            <div class="card-section">

                <div class="section-label">
                    Zdroj fakturace
                </div>

                <p>
                    <strong>Zakázka:</strong>
                    <?= e($workOrder['title'] ?? '') ?>
                </p>

            </div>

            <form method="post">

                <?= Csrf::getField() ?>

                <h3>Faktura</h3>

                <div class="form-group">
                    <label>Název faktury</label>
                    <input
                        type="text"
                        name="title"
                        value="<?= e($invoice['title'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Datum vystavení</label>
                    <input
                        type="date"
                        name="issued_at"
                        value="<?= e($invoice['issued_at'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Datum splatnosti</label>
                    <input
                        type="date"
                        name="due_date"
                        value="<?= e($invoice['due_date'] ?? '') ?>"
                        required
                    >
                </div>

                <h3>Odběratel</h3>
                
<?php
/** -- tady by mělo dojit k require: --*/
$data = $customer;
require __DIR__ . '/../contacts/_contactForm.php'; 
?>

<div class="form-group">
    <label><input type="checkbox" name="save_customer" value="1" <?= !isset($invoice['save_customer']) || $invoice['save_customer'] === true ? 'checked' : '' ?> > Uložit nového zákazníka / uložit případné změny u zákazníka </label>
</div>
                <h3>Položky faktury</h3>

                <table class="table">

                    <thead>
                        <tr>
                            <th>Popis položky</th>
                            <th>Množství</th>
                            <th>Měrná Jednotka</th>
                            <th>Cena MJ</th>
                            <th>Cena celkem</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach ($items as $i => $item): ?>
                         <tr>

                            <td>
                                <input
                                    type="hidden"
                                    name="items[<?= $i ?>][task_id]"
                                    value="<?= (int)$item['task_id'] ?>"
                                >
                               <textarea
                                    name="items[<?= $i ?>][title]"
                                    rows="4"
                                ><?= e($item['title'] ?? '') ?></textarea>
                                <br>
                            </td>

                            <td>
                                <input
                                    type="number"
                                    name="items[<?= $i ?>][minutes]"
                                    value="<?= formatMinutes((int)($item['minutes'] ?? 0)) ?>"
                                > minut<br>
                            </td>
                            <td><select></select></td>

                            <td>
                                <input
                                    type="number"
                                    name="items[<?= $i ?>][kilometers]"
                                    value="<?= (int) ($item['kilometers'] ?? 0) ?>"
                                ><br>
                                <label>
                                    <input
                                        type="checkbox"
                                        name="items[<?= $i ?>][visible_km]"
                                        value="1"
                                        <?= isset($item['visible_km']) ? 'checked' : '' ?>
                                    >
                                    Zobrazit km na faktuře
                                </label>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

                <div class="form-group">
                    <label>Poznámka</label>

                    <textarea
                        name="note"
                        rows="4"
                    ><?= e($invoice['note'] ?? '') ?></textarea>
                </div>

                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn btn-primary">
                        Vytvořit fakturu
                    </button>

                    <a
                        href="<?= Url::to('/{tenant}/workbench/#main') ?>"
                        class="btn btn-secondary">
                        Zpět
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>