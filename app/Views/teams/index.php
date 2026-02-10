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

<h1>
    <?= $mode === 'inactive' ? 'Deaktivované týmy' : 'Týmy' ?>
</h1>

<div class="teams-list compact">

<?php foreach ($view->teams as $team): ?>
    <div class="team-item <?= $team['active'] ? '' : 'is-inactive' ?>">

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
            <?php foreach ($team['members'] as $m): ?>
                <div class="member-line role-<?= e($m['role_in_team']) ?>">
                    <?= e($m['last_name'] . ' ' . $m['first_name']) ?>
                </div>
            <?php endforeach ?>
        </div>

        <!-- AKCE -->
        <div class="team-actions">
            <?php if ($mode === 'active' && Access::can('teams.edit')): ?>
                <a class="action-link edit"
                   href="<?= Url::to('/{tenant}/teams/' . (int)$team['id'] . '/edit/#main') ?>">
                    Upravit tým
                </a>
            <?php endif ?>

            <?php if (Access::can('teams.edit')): ?>
                <a class="action-link toggle"
                   href="<?= Url::to('/{tenant}/teams/toggle/' . (int)$team['id']) ?>"
                   onclick="return confirm('Opravdu chcete změnit stav týmu?')">
                    <?= $team['active'] ? 'Deaktivovat tým' : 'Aktivovat tým' ?>
                </a>
            <?php endif ?>
        </div>

    </div>
<?php endforeach ?>

</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
