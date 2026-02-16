<?php
declare(strict_types=1);

use App\Core\Url;
use App\Core\Access;
$css = '';
//require __DIR__ . '/style.php';
require __DIR__ . '/../layout/header.php';
?>
<?= $css ?>
<div class="ui-alert ui-alert-warning">
    <strong>Upozornění:</strong>
    Uživatelé označení jako neaktivní se nemohou přihlásit.
</div>
<?php if (empty($view->users)): ?>

    <div class="users-empty">
        <strong>Žádní uživatelé</strong>
        <p>Zatím zde není žádný uživatel.</p>
    </div>

<?php else: ?>

<div class="entity-grid">
<?php foreach ($view->users as $user): ?>

    <?php
        $fullName = trim(
            ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')
        );

        $isActive = (bool)($user['active'] ?? true);
    ?>
<div class="card  <?= !$user['active'] ? 'is-inactive' : '' ?>">
    <div class="card-header">
        <span class="card-title"><a href="<?= Url::to('/{tenant}/users/' . (int)$user['id'] . '/detail/#main') ?>"><?= e($fullName ?: 'Bez jména') ?></a></span>
        <span class="badge badge-<?= $isActive ? 'active' : 'inactive' ?>"><?= $isActive ? 'Aktivní' : 'Neaktivní' ?></span>
    </div>
    
    <div class="card-body">
        <div class="meta-list">
            <div class="meta-item">
                <span class="meta-label">Číslo zaměstnance</span>
                <span class="meta-value"><?= e($user['employee_number'] ?? '—') ?></span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Role</span>
                <span class="meta-value"><?= e($user['global_role'] ?? '—') ?></span>
            </div>
        </div>
    </div>
    
    <div class="card-footer">
                    <a href="<?= Url::to('/{tenant}/users/' . (int)$user['id'] . '/edit/#main') ?>" class="btn btn-secondary">✏️ Upravit</a>
            <a href="<?= Url::to('/{tenant}/users/' . (int)$user['id'] . '/detail/#main') ?>" class="btn btn-secondary">🔍 Detail</a>
    </div>
</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
