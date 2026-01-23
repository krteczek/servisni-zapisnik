<?php
declare(strict_types=1);

// views/teams/index.php – přehled týmů

use App\Core\Url;
$filters = $view->filters;
$logs = $view->logs;
?>

<form method="get" class="audit-filter">

<p><strong>Filtrování výpisu logů</strong></p>

<table cellpadding="4" cellspacing="0">
    <tr>
        <td><label for="user_id">Uživatel (ID)</label></td>
        <td>
            <input type="number" id="user_id" name="user_id"
                   value="<?= e($filters['user_id'] ?? '') ?>">
        </td>
    </tr>

    <tr>
        <td><label for="action">Akce</label></td>
        <td>
            <select id="action" name="action">
                <option value="">-- všechny --</option>
                <option value="insert" <?= ($filters['action'] ?? '') === 'insert' ? 'selected' : '' ?>>insert</option>
                <option value="update" <?= ($filters['action'] ?? '') === 'update' ? 'selected' : '' ?>>update</option>
            </select>
        </td>
    </tr>

    <tr>
        <td><label for="table">Entita</label></td>
        <td>
            <input type="text" id="table" name="table"
                   value="<?= e($filters['table'] ?? '') ?>">
        </td>
    </tr>

    <tr>
        <td><label for="ip">IP adresa</label></td>
        <td>
            <input type="text" id="ip" name="ip"
                   value="<?= e($filters['ip'] ?? '') ?>">
        </td>
    </tr>

    <tr>
        <td><label>Datum</label></td>
        <td>
            od
            <input type="date" name="from" value="<?= e($filters['from'] ?? '') ?>">
            do
            <input type="date" name="to" value="<?= e($filters['to'] ?? '') ?>">
        </td>
    </tr>

    <tr>
        <td></td>
        <td>
            <button>Filtrovat</button>
            <a href="<?= Url::to('/admin/audit') ?>">Zrušit filtry</a>
        </td>
    </tr>
</table>

</form>
