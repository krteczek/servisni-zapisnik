<?php
declare(strict_types=1);
$css = '';
require __DIR__ . '/style.php';

require __DIR__ . '/../layout/header.php';

use App\Core\Url;
use App\Core\Csrf;
use App\Core\Access;
$data = $view->data; 
?>
<style>
<?= $css ?>
</style>
<form method="post" action="">
    <?= Csrf::getField() ?>

    <table class="form-table">
        <tr>
            <th><label for="external_number">Externí číslo</label></th>
            <td>
                <input
                    id="external_number"
                    name="external_number"
                    value="<?= e($data['external_number'] ?? '') ?>"
                >
            </td>
        </tr>

        <tr>
            <th><label for="title">Název <span class="req">*</span></label></th>
            <td>
                <input
                    id="title"
                    name="title"
                    required
                    value="<?= e($data['title'] ?? '') ?>"
                >
            </td>
        </tr>

        <tr>
            <th><label for="description">Popis</label></th>
            <td>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                ><?= e($data['description'] ?? '') ?></textarea>
            </td>
        </tr>

        <tr>
            <th><label for="source">Zdroj</label></th>
            <td>
                <select id="source" name="source">
                    <option value="email"><?= te('email') ?></option>
                    <option value="phone"><?= te('phone') ?></option>
                    <option value="personal"><?= te('personal') ?></option>
                    <option value="system"><?= te('system') ?></option>
                </select>
            </td>
        </tr>

        <tr>
            <th><label for="requested_by">Požadoval</label></th>
            <td>
                <input
                    id="requested_by"
                    name="requested_by"
                    value="<?= e($data['requested_by'] ?? '') ?>"
                >
            </td>
        </tr>

        <tr>
            <th><label for="contact">Kontaktní osoba</label></th>
            <td>
                <input
                    id="contact"
                    name="contact"
                    value="<?= e($data['contact'] ?? '') ?>"
                >
            </td>
        </tr>


        <tr>
            <th><label for="priority">Priorita</label></th>
            <td>
                <select id="priority" name="priority">
                    <option value="low">Nízká</option>
                    <option value="normal" selected>Normální</option>
                    <option value="high">Vysoká</option>
                    <option value="emergency">Havárie</option>
                </select>
            </td>
        </tr>

        <tr>
            <th></th>
            <td class="form-actions">
                <button type="submit" class="btn btn-primary">Uložit</button>
                <a href="<?= Url::to('/{tenant}/work-orders') ?>/#main" class="btn btn-secondary">
                    Zpět na přehled
                </a>
            </td>
        </tr>
    </table>
</form>

<?php require __DIR__ . '/../layout/footer.php'; ?>
