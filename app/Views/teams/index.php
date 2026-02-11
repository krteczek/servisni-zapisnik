<?php
declare(strict_types=1);

// views/teams/index.php – přehled týmů

use App\Core\Url;
use App\Core\Access;

$css = '';
require __DIR__ . '/style.php';
require __DIR__ . '/../layout/header.php';

/** @var string $view->mode 'active' | 'inactive' */
$mode = $view->mode ?? 'active';
?>

<?= $css ?>

<?php if (empty($view->teams)): ?>

    <div class="teams-empty">
        <?php if ($mode === 'inactive'): ?>
            <strong>Žádné deaktivované týmy</strong>
            <p>Zatím zde není žádný deaktivovaný tým.</p>
        <?php else: ?>
            <strong>Žádné týmy</strong>
            <p>Zatím zde není vytvořen žádný tým.</p>
        <?php endif ?>
    </div>

<?php else: ?>
<div class="teams-list compact">
        <?php foreach ($view->teams as $team): ?>
            <!-- tvůj team-item -->
	<div class="team-item <?= $team['active'] ? '' : 'is-inactive' ?>" style="--team-color: <?= e($team['color']) ?>;">

        <!-- HLAVIČKA -->
        <div class="team-header">
            <div class="team-title">
                <span class="team-color"
                      style="--team-color: <?= e($team['color']) ?>"></span>

                <strong class="team-name">
                    <?= e($team['name']) ?>
                </strong>
            </div>
        </div>

        <!-- ČLENOVÉ -->
<div class="team-members">
    <?php if (empty($team['members'])): ?>
        <div class="team-members-empty">
            V tomto týmu zatím nikdo není.
        </div>
    <?php else: ?>
        <?php foreach ($team['members'] as $m): ?>
            <div class="member-line role-<?= e($m['role_in_team']) ?>">
                <?= e($m['last_name'] . ' ' . $m['first_name']) ?>
            </div>
        <?php endforeach ?>
    <?php endif ?>
</div>

        <!-- AKCE -->
        <div class="team-actions">
            <?php if ($mode === 'active' && Access::can('teams.edit')): ?>
                <a class="action-link edit"
                   href="<?= Url::to('/{tenant}/teams/' . (int)$team['id'] . '/edit/#main') ?>"
                   title="Upravit tým"> ✏️

                </a>
            <?php endif ?>
<?php if (Access::can('teams.edit')): ?>
    <a class="action-link toggle"
       href="<?= Url::to('/{tenant}/teams/toggle/' . (int)$team['id']) ?>"
       title="<?= $team['active'] ? 'Deaktivovat tým' : 'Aktivovat tým' ?>"
       onclick="return confirm('Opravdu chcete změnit stav týmu?')">

        <?= $team['active'] ? '🔒' : '🔓' ?>

    </a>
<?php endif ?>

        </div>
         </div>   
        <?php endforeach ?>
    </div>

<?php endif ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
