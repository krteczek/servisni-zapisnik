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

<div class="create-container">

    <div class="card">

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
                <input 
                    type="hidden"
                    name="contact_id"
                    value="<?= (int)($customer['id'] ?? 0) ?>"
                >
                <div class="form-group">
                    <label>Název firmy</label>
                    <input
                        type="text"
                        name="customer[company_name]"
                        value="<?= e($customer['company_name'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label>IČO</label>
                    <input
                        type="text"
                        name="customer[ico]"
                        value="<?= e($customer['ico'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label>DIČ</label>
                    <input
                        type="text"
                        name="customer[dic]"
                        value="<?= e($customer['dic'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label>Ulice</label>
                    <input
                        type="text"
                        name="customer[street]"
                        value="<?= e($customer['street'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label>Město</label>
                    <input
                        type="text"
                        name="customer[city]"
                        value="<?= e($customer['city'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label>PSČ</label>
                    <input
                        type="text"
                        name="customer[zip]"
                        value="<?= e($customer['zip'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label>Země</label>
                    <input
                        type="text"
                        name="customer[country]"
                        value="<?= e($customer['country'] ?? '') ?>"
                    >
                </div>

                <h3>Položky faktury</h3>

                <table class="table">

                    <thead>
                        <tr>
                            <th>Úkol</th>
                            <th>Čas</th>
                            <th>Km</th>
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
                                    value="<?= (int)($item['minutes'] ?? 0) ?>"
                                > minut<br>
                                <label>
                                    <input
                                        type="checkbox"
                                        name="items[<?= $i ?>][visible_time]"
                                        value="1"
                                        <?= isset($item['visible_time']) ? 'checked' : '' ?>
                                    >
                                    Zobrazit čas na faktuře
                                </label>

                            </td>

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