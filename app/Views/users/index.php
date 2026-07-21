<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
use App\Core\Access;

require __DIR__ . '/../layout/header.php';

//var_dump($view->users);
?>

<div class="ui-alert ui-alert-warning">
    <strong>Upozornění:</strong>
    Uživatelé označení jako neaktivní se nemohou přihlásit.
</div>
<?php if ($view->users === []): ?>

    <div class="users-empty">
        <strong>Žádní uživatelé</strong>
        <p>Zatím zde není žádný uživatel.</p>
    </div>

<?php else: ?>

<div class="entity-grid">
<?php foreach ($view->users as $user): ?>

    <?php
        $fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        $fullName = $fullName === '' ? 'Bez jména' : $fullName;

		$isActive = a($user);
    ?>
<div class="card  <?= $isActive ?>">
    <div class="card-header">
        <span class="card-title"><a href="<?= Url::to('/{tenant}/users/' . (int)$user['id'] . '/detail/#main') ?>"><?= e($fullName) ?></a></span>
        <span class="badge badge-<?= e($isActive) ?>"><?= te($isActive) ?></span>
    </div>
    
    <div class="card-body">
        <div class="meta-list">
            <div class="meta-item">
                <span class="meta-label">Telefón: </span>
                <span class="meta-value"><?= e($user['telefon'] ?? 'Neuveden') ?></span>
            </div>

            <div class="meta-item">
                <span class="meta-label">Číslo zaměstnance: </span>
                <span class="meta-value"><?= e($user['employee_number'] ?? '—') ?></span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Role: </span>
                <span class="meta-value"><?= te($user['global_role'] ?? '—') ?></span>
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
