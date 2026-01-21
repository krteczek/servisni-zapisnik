<?php
declare(strict_types=1);

/** @var array $logs */
/** @var array $filters */

use App\Core\Url;
$filters = $view->filters;
$logs = $view->logs;
require __DIR__ . '/../../layout/header.php';
?>

<?php require __DIR__ . '/_filter.php'; ?>

<table border="1" cellpadding="6" cellspacing="0">
    <thead>
        <tr>
            <th>Čas</th>
            <th>Uživatel</th>
            <th>Akce</th>
            <th>Entita</th>
            <th>ID</th>
            <th>Změny</th>
            <th>IP</th>
            <th>Akce</th>
        </tr>
    </thead>

    <tbody>
    
    <?php if(is_array($logs)):
    	foreach ($logs as $log): ?>
        <?php
            //$diff = json_decode($log['diff'] ?? '', true);
            $diff = $log['diff']
    ? json_decode($log['diff'], true)
    : null;
        ?>
        <tr>
            <td><?= e($log['created_at']) ?></td>
            <td>
                <?= (int) $log['user_id'] ?><br>
                <small><?= e($log['user_email']) ?></small>
            </td>
            <td><?= e($log['action']) ?></td>
            <td><?= e($log['entity']) ?></td>
            <td><?= (int) $log['entity_id'] ?></td>

            <td>
                <?php if (!$diff): ?>
                    <em>– beze změn –</em>
                <?php else: ?>
                    <ul style="margin:0; padding-left:16px">
                        <?php foreach ($diff as $field => $change): ?>
                            <?php if ($field === 'password'): ?>
                                <li><strong>heslo:</strong> změněno</li>
                            <?php else: ?>
                             <li>
    <strong><?= e($field) ?>:</strong>
<span style="color:#a00"><?= formatValue($change['from']) ?></span>
→  
<span style="color:#060"><span style="color:#060"><?= formatValue($change['to']) ?></span>

</li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </td>

            <td><?= e($log['ip_address']) ?></td>

            <td>
                <a href="<?= Url::to('/admin/audit/' . (int)$log['id']) ?>">
                    detail
                </a>
            </td>
        </tr>
    <?php endforeach; 
		endif;    
    ?>
    </tbody>
</table>
<?php require __DIR__ . '/../../layout/footer.php'; ?>