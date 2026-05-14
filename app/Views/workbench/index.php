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
        <button class="tab-button" data-target="my-tasks" title="úkoly týmů, kterých jsem členem">Otevřené úkoly pro mé týmy</button>
        <button class="tab-button" data-target="other-tasks" title="úkoly týmů, kterých nejsem členem">Otevřené úkoly pro ostatní týmy</button>
        <button class="tab-button" data-target="billing-tasks" title="úkoly, které splňují systémové podmínky pro fakturaci">Připraveno k fakturaci</button>
        <button class="tab-button" data-target="prepare-to-done-tasks" title="úkoly, které lze uzavřít">K uzavření</button>
        <button class="tab-button" data-target="prepare-to-cancel-tasks" title="úkoly, které lze stornovat">Ke stornování</button>
        <button class="tab-button" data-target="recurring-tasks" title="opakované úkoly">Opakované úkoly</button>
    </div>


    <div id="my-tasks" class="tab-content">
        <!-- úkoly pro mé týmy -->
        <?php require __DIR__ . '/partials/my_teams_tasks.php'; ?>
        
    </div>
    
    <div id="other-tasks" class="tab-content" hidden>
        <!-- úkoly pro ostatní týmy -->
        <?php require __DIR__ . '/partials/other_teams_tasks.php'; ?>
    </div>
    <div id="billing-tasks" class="tab-content" hidden>
        <!-- úkoly pro mé týmy k fakturaci -->
        <h2>Seznam úkolů připravených k fakturaci</h2>
        <p>Lorem ipsum dolor sit ... </p>
    </div>


    <div id="prepare-to-done-tasks" class="tab-content" hidden>
        <h2>Seznam úkolů připravených k uzavření</h2>
        <p>Sed pulvinar mi at mollis...</p>
    </div>


    <div id="prepare-to-cancel-tasks" class="tab-content" hidden>
        <h2>Seznam úkolů připravených ke stornování</h2>
        <p>Maecenas sit amet purus at turpis sceleriivamus ... </p>
    </div>


    <div id="recurring-tasks" class="tab-content" hidden>
        <h2>Seznam opakujících se úkolů</h2>
        <p>Integer fringilla, arcu vitae consequat congue,</p>
    </div>


</div><!-- .workbench-container -->
<?php require __DIR__ . '/../layout/footer.php'; ?>