<?php
declare(strict_types=1);

// views/teams/create.php – vytvoření týmu

use App\Core\Url;
use App\Core\Csrf;

$css = '';
require __DIR__ . '/style.php';
require __DIR__ . '/../layout/header.php';
?>

<?= $css ?>

<?php if (!empty($view->error)): ?>
    <p style="color:#c62828; max-width:900px; margin:1rem auto;">
        <?= htmlspecialchars($view->error) ?>
    </p>
<?php endif; ?>

<form method="post" action="">
    <?= Csrf::getField() ?>

    <table class="form-table">
        <tr>
            <th>
                <label for="name">Název týmu <span class="req">*</span></label>
            </th>
            <td>
                <input
                    id="name"
                    name="name"
                    required
                    value="<?= e($view->data['name'] ?? '') ?>"
                >
            </td>
        </tr>

        <tr>
            <th>
                <label for="color">Barva týmu</label>
            </th>
            <td>
                <input
                    id="color"
                    type="color"
                    name="color"
                    value="<?= e($view->data['color'] ?? '#2196F3') ?>"
                    style="height: 38px; padding: 2px;"
                >
            </td>
        </tr>

        <tr>
            <th></th>
            <td class="form-actions">
                <button type="submit" class="btn btn-primary">
                    Vytvořit tým
                </button>

                <a href="<?= Url::to('/{tenant}/teams') ?>/#main"
                   class="btn btn-secondary">
                    Zpět na přehled
                </a>
            </td>
        </tr>
    </table>
</form>

<?php require __DIR__ . '/../layout/footer.php'; ?>
