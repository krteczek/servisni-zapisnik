<?php
declare(strict_types=1); 

/** @var \App\Core\ViewContext $view */

require __DIR__ . '/../layout/header.php';

use App\Core\Url;

$data = $view->data;
//var_dump($data);
?>

<h2>Otevřené úkoly pro mé týmy</h2>
<table>
    <thead>
        <tr>
            <th>Název úkolu</th>
            <th>Tým</th>
            <th>Zakázka</th>        
            <th>Akce</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['myBillingTasks'] as $task): ?>
        <tr>
            <td><?= e($task['title']) ?></td>
            <td><?= e($task['title']) ?></td>
            <td><?= e($task['title']) ?></td>
            <td><?= e($task['title']) ?></td>
        </tr>
        <?php endforeach; ?>
     </tbody>
</table>