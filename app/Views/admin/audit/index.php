<?php
declare(strict_types=1);

// views/teams/index.php – přehled týmů

use App\Core\Url;
$filters = $view->filters;
$logs = $view->logs;
require __DIR__ . '/../../layout/header.php';


?>


<form method="get" action="">
    <fieldset>
        <legend>Filtr audit logu</legend>

        <label>
            Uživatel ID:
            <input type="number" name="user_id" value="<?= htmlspecialchars($filters['user_id'] ?? '') ?>">
        </label>

        <label>
            Akce:
            <select name="action">
                <option value="">– všechny –</option>
                <option value="create" <?= ($filters['action'] ?? '') === 'create' ? 'selected' : '' ?>>create</option>
                <option value="update" <?= ($filters['action'] ?? '') === 'update' ? 'selected' : '' ?>>update</option>
                <option value="delete" <?= ($filters['action'] ?? '') === 'delete' ? 'selected' : '' ?>>delete</option>
                <option value="error"  <?= ($filters['action'] ?? '') === 'error'  ? 'selected' : '' ?>>error</option>
            </select>
        </label>

        <label>
            Tabulka:
            <input type="text" name="table" value="<?= htmlspecialchars($filters['table'] ?? '') ?>">
        </label>

        <label>
            Od:
            <input type="date" name="from" value="<?= htmlspecialchars($filters['from'] ?? '') ?>">
        </label>

        <label>
            Do:
            <input type="date" name="to" value="<?= htmlspecialchars($filters['to'] ?? '') ?>">
        </label>

        <label>
            IP:
            <input type="text" name="ip" value="<?= htmlspecialchars($filters['ip'] ?? '') ?>">
        </label>

        <button type="submit">Filtrovat</button>
    </fieldset>
</form>

<table border="1" cellpadding="4">
    <tr>
        <th>Čas</th>
        <th>Uživatel id</th>
        <th>uživatel email</th>
        <th>Akce</th>
        <th>Řádek</th>
        <th>Původní data</th>
        <th>Nová data</th>
        <th>IP</th>
        <th>Prohlížeč</th>
    </tr>
<?php var_dump($logs); ?>
    <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= htmlspecialchars($log['created_at']) ?></td>
            <td><?= (int)$log['user_id'] ?></td>
            <td><?= (int)$log['user_email'] ?></td>
            <td><?= htmlspecialchars($log['action']) ?></td>
            <td><?= (int)$log['entity_id'] ?></td>
            <td><?= htmlspecialchars($log['old_values']) ?></td>
            <td><?= htmlspecialchars($log['new_values']) ?></td>
            <td><?= htmlspecialchars($log['ip_address']) ?></td>
            <td><?= htmlspecialchars($log['user_agent']) ?></td>
            
            
            <td>
                <a href="/admin/audit/<?= (int)$log['id'] ?>">detail</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

