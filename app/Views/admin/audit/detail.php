<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */


use App\Core\Url;


require __DIR__ . '/../../layout/header.php';
$log = $view->data;
$diff = $log['diff']
    ? json_decode($log['diff'], true)
    : null;
?>

<table border="1" cellpadding="6" cellspacing="0">
    <tr>
        <th>ID</th>
        <td><?= (int) $log['id'] ?></td>
    </tr>
    <tr>
        <th>Čas</th>
        <td><?= e($log['created_at']) ?></td>
    </tr>
    <tr>
        <th>Uživatel</th>
        <td><?= (int) $log['user_id'] ?></td>
    </tr>
    <tr>
        <th>Akce</th>
        <td><?= e($log['action']) ?></td>
    </tr>
    <tr>
        <th>Entita</th>
        <td><?= e($log['entity']) ?></td>
    </tr>
    <tr>
        <th>ID entity</th>
        <td><?= (int) $log['entity_id'] ?></td>
    </tr>
    <tr>
        <th>IP adresa</th>
        <td><?= e($log['ip_address']) ?></td>
    </tr>
    <tr>
        <th>User agent</th>
        <td style="font-size:12px; opacity:.7">
            <?= e($log['user_agent']) ?>
        </td>
    </tr>
</table>

<h3>Změny</h3>

<?php if ($diff === null || $diff === []): ?>
    <p><em>Žádné změny</em></p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0" style="max-width:1100px">
        <thead>
            <tr>
                <th>Položka</th>
                <th>Původní hodnota</th>
                <th>Nová hodnota</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($diff as $field => $change): ?>
                <tr>
                    <td><?= e($field) ?></td>

                    <?php if ($field === 'password'): ?>
                        <td colspan="2"><em>heslo změněno</em></td>
                    <?php else: ?>
                        <td class="old"><?= formatValue($change['from'] ?? null) ?></td>
                        <td class="new"><?= formatValue($change['to'] ?? null) ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<p>
    <a href="<?= Url::to('/admin/audit') ?>">← zpět na audit</a>
</p>

<?php require __DIR__ . '/../../layout/footer.php'; ?>
