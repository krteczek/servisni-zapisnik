<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Csrf;
use App\Core\Url;
require __DIR__ . '/../layout/header.php';
$task      = $view->data['task'] ?? [];
$workOrder = $view->data['workOrder'] ?? [];
$customer  = $view->data['customer'] ?? [];
$invoice   = $view->data['invoiceDraft'] ?? [];
$errors    = $view->errors ?? [];
?>

<div class="create-container">

    <div class="card">

        <div class="card-header">
            Vytvoření faktury z úkolu
        </div>

        <div class="card-body">

            <div class="card-section">
                <div class="section-label">
                    Zdroj fakturace
                </div>

                <p>
                    <strong>Zakázka:</strong>
                    <?= e($workOrder['title'] ?? '') ?>
                </p>

                <p>
                    <strong>Úkol:</strong>
                    <?= e($task['title'] ?? '') ?>
                </p>

                <p>
                    <strong>Dokončeno:</strong>
                    <?= formatCzDate($task['done_at'] ?? '') ?>
                </p>

                <p>
                    <strong>Odpracováno:</strong>
                    <?= formatMinutes($task['total_minutes'] ?? 0) ?>
                </p>

                <p>
                    <strong>Kilometry:</strong>
                    <?= (int) ($task['total_kilometers'] ?? 0) ?>
                </p>
            </div>

            <form method="post">

                <?= Csrf::getField() ?>

                <h3>Odběratel</h3>

                <div class="form-group">
                    <label>Název odběratele</label>
                    <input
                        type="text"
                        name="customer_name"
                        value="<?= e($invoice['customer_name'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Adresa</label>

                    <textarea
                        name="customer_address"
                        rows="4"
                    ><?= e($invoice['customer_address'] ?? '') ?></textarea>
                </div>

                <h3>Položky faktury</h3>

                <table>
                    <thead>
                        <tr>
                            <th>Položka</th>
                            <th>Množství</th>
                            <th>Jednotka</th>
                            <th>Cena</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($invoice['items'] as $i => $item): ?>
                        <tr>

                            <td>
                                <input
                                    type="text"
                                    name="items[<?= $i ?>][label]"
                                    value="<?= e($item['label']) ?>"
                                >
                            </td>

                            <td>
                                <input
                                    type="number"
                                    step="0.01"
                                    name="items[<?= $i ?>][qty]"
                                    value="<?= e($item['qty']) ?>"
                                >
                            </td>

                            <td>
                                <input
                                    type="text"
                                    name="items[<?= $i ?>][unit]"
                                    value="<?= e($item['unit']) ?>"
                                >
                            </td>

                            <td>
                                <input
                                    type="number"
                                    step="0.01"
                                    name="items[<?= $i ?>][price]"
                                    value="<?= e($item['price']) ?>"
                                >
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

<?php require __DIR__ . '/../layout/footer.php';
