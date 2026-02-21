<?php
declare(strict_types=1);

// views/teams/index.php – přehled týmů

use App\Core\Url;
use App\Core\Access;

$css = '';
//require __DIR__ . '/style.php';
require __DIR__ . '/../layout/header.php';

/** @var string $view->mode 'active' | 'inactive' */
$mode = $view->mode ?? 'active';
?>

<?= $css ?>

<?php if (empty($view->teams)): ?>

    <div class="ui-alert ui-alert-warning">
        <?php if ($mode === 'inactive'): ?>
            <strong>Žádné deaktivované týmy</strong>
            <p>Zatím zde není žádný deaktivovaný tým.</p>
        <?php else: ?>
            <strong>Žádné týmy</strong>
            <p>Zatím není vytvořen žádný tým. Nový tým vytvoříte snadno <a href="<?= Url::to('/{tenant}/teams/create/#main') ?>">zde</a>.</p>
        <?php endif ?>
    </div>

<?php else: ?>

<div class="ui-alert ui-alert-warning">
    <strong>Upozornění:</strong>
    Pokud hledáte tým, který zde není, zkuste se podívat do 
    <a href="<?= Url::to('/{tenant}/teams/inactive/#main') ?>">deaktivovaných týmů</a>
</div>

<div class="entity-grid">
    <?php foreach ($view->teams as $team): ?>
        <div class="card team-card <?= $team['active'] ? '' : 'is-inactive' ?>" 
             style="--team-color: <?= e($team['color']) ?>;">

            <!-- HLAVIČKA KARTY -->
            <div class="card-header">
                <span class="card-title"><a href="<?= Url::to('/{tenant}/teams/' . (int)$team['id'] . '/edit/#main') ?>" 
                   class="card-title"
                   title="Upravit tým">
                    <?= e($team['name']) ?>
                </a></span>
                <span class="badge <?= $team['active'] ? 'badge-active' : 'badge-inactive' ?>">
                    <?= $team['active'] ? 'Aktivní' : 'Neaktivní' ?>
                </span>
            </div> 

            <!-- TĚLO KARTY S METADATY -->
            <div class="card-body">
                <div class="meta-list">
                    <!-- ČLENOVÉ JAKO METADATA -->
                    <div class="meta-item">
                        <span class="meta-label-header">Členové</span>
                        <div class="meta-value">
                            <?php if (empty($team['members'])): ?>
                                <span class="team-members-empty">Zatím nikdo</span>
                            <?php else: ?>
                                <?php foreach ($team['members'] as $m): ?>
                                    <div class="member-line role-<?= e($m['role_in_team']) ?>">
                                        <?= e($m['last_name'] . ' ' . $m['first_name']) ?>
<span class="team-dots">                                        
<?php foreach ($view->userTeams[$m['id']] ?? [] as $t): ?>
	<span class="team-dot"
	      title="Je členem týmu: <?= e($t['name']) ?>"
	      style="background-color: <?= e($t['color']) ?>"></span>
<?php endforeach ?>
</span>
                                    </div>
                                <?php endforeach ?>
                            <?php endif ?>
                        </div>
                    </div>
                    
                    <!-- Další metadata můžou přibýt -->
                </div>
            </div>

            <!-- PATIČKA KARTY S AKCEMI -->
            <div class="card-footer">
                <div class="actions">
                    <?php if ($mode === 'active' && Access::can('teams.edit')): ?>
                        <a class="btn btn-secondary"
                           href="<?= Url::to('/{tenant}/teams/' . (int)$team['id'] . '/edit/#main') ?>"
                           title="Upravit tým">
                            ✏️ Upravit
                        </a>
                    <?php endif ?>
                    
                    <?php if (Access::can('teams.edit')): ?>
                        <a class="btn btn-secondary"
                           href="<?= Url::to('/{tenant}/teams/toggle/' . (int)$team['id']) ?>"
                           title="<?= e($team['active'] ? 'Deaktivovat tým' : 'Aktivovat tým') ?>"
                           onclick="return confirm('Opravdu chcete změnit stav týmu?')">
                            <?= e($team['active'] ? '🔒 Deaktivovat' : '🔓 Aktivovat') ?>
                        </a>
                    <?php endif ?>
                </div>
            </div>

        </div>   
    <?php endforeach ?>
</div>
<?php endif ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>