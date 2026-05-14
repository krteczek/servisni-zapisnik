<?php
declare(strict_types=1); 

/** @var \App\Core\ViewContext $view */


use App\Core\Url;

$data = $view->data;
// var_dump($data);
?>

<h2>Připraveno k uzavření</h2>

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
        <?php foreach ($data['otherReadyToDoneTasks'] as $task): ?>
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