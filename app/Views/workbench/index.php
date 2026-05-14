<?php
declare(strict_types=1); 

/** @var \App\Core\ViewContext $view */

require __DIR__ . '/../layout/header.php';

use App\Core\Url;

$data = $view->data;
//var_dump($data);
?>

<div class="workbench-container">
    <h2>Moje týmy</h2>
    <?php if (empty($data['myTeams'])): ?>
        <p>Nejste členem žádného týmu.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($data['myTeams'] as $team): ?>
                <li><span class="team-dot" style="background: <?= e($team['color']) ?>"></span><?= e($team['name']) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <h2>Ostatní týmy</h2>
    <?php if (empty($data['otherTeams'])): ?>
        <p>Neexistují žádné další týmy.</p> 
    <?php else: ?>
        <ul>
            <?php foreach ($data['otherTeams'] as $team): ?>
                <li><span class="team-dot" style="background: <?= e($team['color']) ?>"></span><?= e($team['name']) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h2>Důležité akce</h2>
    <div>
        <button class="tab-button" data-target="myTabsButtons" title="Moje Akce">Moje Akce</button>
        <button class="tab-button" data-target="otherTabsButtons" title="Akce pro ostatní týmy">Ostatní týmy</button>
    </div>
    <div>
        <div id="myTabsButtons"  class="tab-content">
                <button class="tab-button" data-target="my-tasks" title="úkoly týmů, kterých jsem členem">Otevřené úkoly (mé) [<?= count($data['myTeamTasks']) ?>]</button>
                <button class="tab-button" data-target="my-prepare-to-done" title="moje úkoly a zakázky, které lze uzavřít">K uzavření (mé) [<?= count($data['myReadyToDoneTasks']) ?>]</button>
                <button class="tab-button" data-target="my-prepare-to-cancel" title="úkoly, které lze stornovat">Ke stornování (mé) []</button>
                <button class="tab-button" data-target="my-billing" title="úkoly, které splňují systémové podmínky pro fakturaci">Připraveno k fakturaci (mé)</button>
        </div>

        <div id="otherTabsButtons"  class="tab-content" hidden>
            <button class="tab-button" data-target="other-tasks" title="úkoly týmů, kterých nejsem členem">Otevřené úkoly (ostatní) [<?= count($data['otherTeamTasks']) ?>]</button>
            <button class="tab-button" data-target="other-prepare-to-done" title="ostatní úkoly a zakázky, které lze uzavřít">K uzavření (ostatní) [<?= count($data['otherReadyToDoneTasks']) ?>]</button>
            <button class="tab-button" data-target="other-prepare-to-cancel" title="úkoly, které lze stornovat">Ke stornování (ostatní)</button>
            <button class="tab-button" data-target="other-billing" title="úkoly, které splňují systémové podmínky pro fakturaci">Připraveno k fakturaci (ostatní)</button>
        </div>
    </div>




    <div id="my-tasks" class="tab-content">
        <!-- úkoly pro mé týmy -->
        <?php require __DIR__ . '/partials/my_teams_tasks.php'; ?>
        
    </div>
    
    <div id="my-prepare-to-done" class="tab-content" hidden>
        <!-- Připraveno k uzavření -->
        <?php require __DIR__ . '/partials/my_prepare_to_done.php'; ?>
    </div>

    <div id="my-prepare-to-cancel" class="tab-content" hidden>
        <h2>Připraveno k stornování</h2>
        <p>Sed pulvinar mi at mollis...</p>
    </div>

    <div id="my-billing" class="tab-content" hidden>
        <!-- úkoly pro mé týmy k fakturaci -->
        <h2>Seznam úkolů připravených k fakturaci</h2>
        <p>Lorem ipsum dolor sit ... </p>
    </div>


    <!-- Akce pro ostatní týmy -->
    <div id="other-tasks" class="tab-content" hidden>
        <!-- úkoly pro ostatní týmy -->
        <?php require __DIR__ . '/partials/other_teams_tasks.php'; ?>
    </div>

     <div id="other-prepare-to-done" class="tab-content" hidden>
        <!-- Připraveno k uzavření -->
        <?php require __DIR__ . '/partials/other_prepare_to_done.php'; ?>
    </div>

    <div id="other-prepare-to-cancel" class="tab-content" hidden>
        <h2>Seznam úkolů a zakázek připravených ke stornování</h2>
        <p>Maecenas sit amet purus at turpis sceleriivamus ... </p>
    </div>

   <div id="other-billing" class="tab-content" hidden>
        <!-- úkoly pro mé týmy k fakturaci -->
        <h2>Seznam úkolů připravených k fakturaci</h2>
        <p>Lorem ipsum dolor sit ... </p>
    </div>



</div><!-- .workbench-container -->
<?php require __DIR__ . '/../layout/footer.php'; ?>