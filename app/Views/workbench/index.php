<?php
declare(strict_types=1); 

/** @var \App\Core\ViewContext $view */

require __DIR__ . '/../layout/header.php';

use App\Core\Url;

$data = $view->data;
//var_dump($data);
?>
<style>
    table, th, td {
        border: 1px solid #ccc;
        border-collapse: collapse;

    }
    tbody tr:nth-child(even) {
        background: #fafafa;
    }

    .open-new-tab {
    margin-left: .35rem;
    opacity: .5;
    text-decoration: none;
}
    .open-new-tab:hover {
        opacity: 1;
    }

    .tabs {
    display: flex;
    gap: .25rem;
    margin-bottom: 1rem;
    border-bottom: 1px solid #ccc;
}

.tab-button {
    padding: .6rem 1rem;
    border: 1px solid #ccc;
    border-bottom: none;
    background: #f3f3f3;
    cursor: pointer;
}

.tab-button.active {
    background: white;
    font-weight: bold;
}
</style>

<div class="workbench-container">
    <h2>Moje týmy</h2>
    <ul>
        <?php foreach ($data['myTeams'] as $team): ?>
            <li><span class="team-dot" style="background: <?= e($team['color']) ?>"></span><?= e($team['name']) ?></li>
        <?php endforeach; ?>
    </ul>
<h2>Důležité akce</h2>
    <div><button class="tab-button" data-target="my-tasks" title="úkoly týmů, kterých jsem členem">Otevřené úkoly pro mé týmy</button>
        <button class="tab-button" data-target="billing-tasks" title="úkoly, které splňují systémové podmínky pro fakturaci">Připraveno k fakturaci</button>
        <button class="tab-button" data-target="prepare-to-done-tasks" title="úkoly, které lze uzavřít">K uzavření</button>
        <button class="tab-button" data-target="prepare-to-cancel-tasks" title="úkoly, které lze stornovat">Ke stornování</button>
        <button class="tab-button" data-target="recurring-tasks" title="opakované úkoly">Opakované úkoly</button>
    </div>


    <div id="my-tasks" class="tab-content">
        <h2>Otevřené úkoly pro mé týmy</h2>
        <table>
            <thead>
                <tr>
                    <th>Priorita</th>
                    <th>Název úkolu</th>
                    <th>Tým</th>
                    <th>Zakázka</th>        
                    <th>Termín</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['myTeamTasks'] as $task): ?>
                    <tr>
                        <td><span class="badge badge-priority-<?= e($task['work_order_priority']) ?>">
                                <?= te($task['work_order_priority']) ?>
                            </span></td>
                        <td><a href="<?= Url::to('/{tenant}/tasks/' .  $task['id'] . '/report/#main') ?>" 
                                    title="Jít na detail tohoto úkolu"
                                    >
                                    <?= e($task['title']) ?></a>

                                <a href="<?= Url::to('/{tenant}/tasks/' .  $task['id'] . '/report/#main') ?>" 
                                    title="Jít na detail tohoto úkolu, otevřít v novém okně"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="open-new-tab"
                                    aria-label="Otevřít detail úkolu v novém panelu"> ( ↗ ) </a>
                        </td>
                        <td><span class="team-dot" style="background: <?= e($task['team_color']) ?>"></span> <?= e($task['team_name']) ?></td>
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
                        
                        <td><?= e($task['due_date'] ?? 'Neuveden') ?></td>
                        
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <!-- Další sekce pro jiné úkoly a opakující se úkoly by šly sem -->

    <div id="billing-tasks" class="tab-content" hidden>
        <h2>Seznam úkolů připravených k fakturaci</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut ac ligula ullamcorper, laoreet ex eget, imperdiet felis. Etiam finibus purus in lacinia auctor. Phasellus at tortor tempus, ullamcorper sem luctus, cursus nunc. Nulla lobortis neque eu diam tempus malesuada. Praesent lobortis est non turpis volutpat, sed aliquam turpis imperdiet. Nulla facilisi. Duis in dolor lectus. Cras sem lacus, congue vel nisi nec, blandit sagittis quam. Donec vestibulum velit at justo tincidunt, non lobortis sapien lacinia. Suspendisse tincidunt eu elit vel malesuada. In mollis, metus non lacinia consectetur, nulla diam tristique nibh, vitae tempor dolor orci id mauris. Aenean lobortis gravida enim, id mattis elit semper vitae. </p>
    </div>


    <div id="prepare-to-done-tasks" class="tab-content" hidden>
        <h2>Seznam úkolů připravených k uzavření</h2>
        <p>Sed pulvinar mi at mollis ultrices. In eget commodo est. Donec nec purus facilisis dui molestie rutrum. Sed ac erat rhoncus, posuere nunc quis, malesuada mi. Morbi id justo et est aliquam consequat. Quisque rutrum facilisis sapien, sit amet tincidunt quam lacinia vitae. Fusce pellentesque facilisis ex, in vehicula nisi tempus ut. Morbi scelerisque finibus purus non malesuada. Etiam bibendum auctor vehicula. Quisque pharetra suscipit tempus. Vivamus sed convallis enim. Suspendisse fringilla risus urna, eget dapibus ipsum rutrum et. Etiam vitae vestibulum urna. Mauris quam urna, lacinia sed egestas eu, auctor sed neque.</p>
    </div>


    <div id="prepare-to-cancel-tasks" class="tab-content" hidden>
        <h2>Seznam úkolů připravených ke stornování</h2>
        <p>Maecenas sit amet purus at turpis scelerisque suscipit. Nulla ornare elementum nibh. Aliquam nibh sapien, volutpat ac mauris non, accumsan suscipit velit. Maecenas ut feugiat urna. Nulla facilisi. Nam a ultrices mi. In commodo elementum quam, aliquet sodales lacus efficitur vitae. Duis lorem nibh, efficitur quis sodales suscipit, consectetur vel mi. Maecenas ac bibendum nulla. Vivamus egestas arcu purus, et euismod ipsum malesuada ultricies. Donec suscipit lobortis erat ac tincidunt. Integer ac elit a ipsum hendrerit egestas. Duis tristique felis ac tortor pellentesque, sed auctor risus dignissim. </p>
    </div>


    <div id="recurring-tasks" class="tab-content" hidden>
        <h2>Seznam opakujících se úkolů</h2>
        <p>Integer fringilla, arcu vitae consequat congue, tellus urna vulputate velit, eget volutpat libero lorem ut risus. Curabitur ut arcu sapien. Integer hendrerit nunc vitae urna sodales, a cursus lacus tristique. Pellentesque rhoncus id erat ac vehicula. Duis tortor odio, aliquet at odio eu, bibendum facilisis neque. Morbi ac elementum justo. Ut auctor luctus elit quis sodales. Nullam hendrerit ante velit, mollis auctor urna rutrum pretium. Donec congue sapien at pretium porttitor. </p>
    </div>


</div><!-- .workbench-container -->
<?php require __DIR__ . '/../layout/footer.php'; ?>