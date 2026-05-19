<?php
declare(strict_types=1); 

/** @var \App\Core\ViewContext $view */


use App\Core\Url;

$data = $view->data;
// var_dump($data);
?>

<h2>Připraveno k uzavření</h2>
<div class="workbench-grid">
<div class="card card-workbench">
    <h3>Úkoly</h3>
<table>
    <thead>
        <tr>
            <th>Název</th>
            <th>Popis</th>
            <th>Status</th>
            <th>Počet reportů</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['myReadyToDoneTasks'] as $task): ?>
            <tr>
                <td><a href="<?= Url::to('/{tenant}/tasks/' .  $task['id'] . '/report/#main') ?>" 
                                    title="Jít na detail úkolu a zkontrolovat nebo přidat reporty."
                                    >
                                    <?= e($task['title']) ?></a></td>
                <td><?= e($task['description'] ?? '') ?></td>
                <td><span class="badge badge-status-<?= e($task['status']) ?>">
                    <?= te($task['status']) ?>
                </span></td>
                <td><?= e($task['assignments_count'] ?? 0) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<div class="card card-workbench">
    <h3>Zakázky</h3>
    <table>
        <thead>
            <tr>
                <th>Název</th>
                <th>Popis</th>
                <th>Status</th>
                <th>Počet reportů</th>
            </tr>
        </thead>
    <tbody>
        <?php foreach ($data['myReadyToDoneOrders'] as $orders): ?>
            <tr>
                <td><a href="<?= Url::to('/{tenant}/work-orders/' .  $orders['id'] . '/detail/#main') ?>" 
                                    title="Jít na detail zakázky a zkontrolovat její úkoly."
                                    >
                                    <?= e($orders['title']) ?></a></td>
                <td><?= e($orders['description'] ?? '') ?></td>
                <td><span class="badge badge-status-<?= e($orders['status']) ?>">
                    <?= te($orders['status']) ?>
                </span></td>
                <td><?= e($orders['can_be_done']) ?></td>
                <td>ee<?= e($orders['can_be_cancelled']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

        </div><!-- .workbench-grid -->
     