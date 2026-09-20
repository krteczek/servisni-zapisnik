<?php
declare(strict_types=1); 

/** @var \App\Core\ViewContext $view */


use App\Core\Url;

$data = $view->WBData;

?>

<h2>Otevřené úkoly pro mé týmy</h2>
<table>
    <thead>
        <tr>
            <th>Priorita</th>
            <th>Název úkolu</th>
            <th>Tým</th>
            <th>Zakázka</th>        
            <th>Termín</th>
            <th>Opakující se</th>
            
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['myTeamTasks'] as $task): ?>
            <tr>
                <td><span class="badge badge-priority-<?= e($task['work_order_priority']) ?>" title="Důležitost úkolu neboli priorita"><?= te($task['work_order_priority']) ?></span></td>
                <td><a href="<?= Url::to('/{tenant}/tasks/' .  $task['id'] . '/report/#main') ?>" 
                            title="Jít na detail úkolu a zkontrolovat nebo přidat reporty."
                            >
                            <?= e($task['title']) ?></a>

                        <a href="<?= Url::to('/{tenant}/tasks/' .  $task['id'] . '/report/#main') ?>" 
                            title="Jít na detail úkolu a zkontrolovat nebo přidat reporty. Otevřít v novém okně"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="open-new-tab"
                            aria-label="Otevřít detail úkolu v novém panelu"> ( ↗ ) </a>
                </td>
                <td><span class="team-dot" style="background: <?= e($task['team_color']) ?>"></span> <span title="Jméno týmu, kterému byl úkol přidělen"><?= e($task['team_name']) ?></span></td>
                <td><a href="<?= Url::to('/{tenant}/work-orders/' .  $task['work_order_id'] . '/detail/#main') ?>" 
                            title="Jít na detail této zakázky" 
                        >
                            <?= e($task['work_order_title']) ?></a>

                        <a href="<?= Url::to('/{tenant}/work-orders/' .  $task['work_order_id'] . '/detail/#main') ?>" 
                            title="Jít na detail této zakázky, otevřít v novém okně" 
                            target="_blank"
                            rel="noopener noreferrer"
                            class="open-new-tab"
                            aria-label="Otevřít detail zakázky v novém panelu"> ( ↗ ) </a>
                </td>
                
                <td><?= formatCzDate($task['due_date'], true) ?></td>
                <td>                        
                    <?php if ($task['task_type'] === 'recurring_master'): ?>
                        <a href="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/recurringEdit/#main') ?>" 
                            title="Upravit nastavení tohoto opakujícího se úkolu"
                            class="btn btn-secondary"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="Upravit nastavení tohoto opakujícího se úkolu v novém panelu"
                            >
                            🔁 ↗
                        </a>
                    <?php elseif ($task['task_type'] === 'recurring_instance'): ?>
                        <span title="Tento úkol byl vygenerován automaticky systémem a nelze ho upravit"
                            aria-label="Tento úkol byl vygenerován automaticky systémem a nelze ho upravit"
                        > ↻ </span>
                    <?php else: ?>
                        ---
                    <?php endif; ?>

                </td>

                
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>