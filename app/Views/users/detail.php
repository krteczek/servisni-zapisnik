<?php
declare(strict_types=1);

use App\Core\Url;
use App\Core\Csrf;
$css = '';
require __DIR__ . '/style.php';
require __DIR__ . '/../layout/header.php';

$user = $view->user ?? [];
?>
<?= $css ?>
<?php if (!empty($user)): ?>

<?php
$fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

if ($user['password_hash'] === null) {
    $stateLabel = 'Čeká na aktivaci';
    $stateClass = 'pending';
} elseif ((int)$user['active'] === 1) {
    $stateLabel = 'Aktivní';
    $stateClass = 'active';
} else {
    $stateLabel = 'Deaktivovaný';
    $stateClass = 'inactive';
}
?>

<div class="user-detail-wrapper">

    <div class="user-detail-card">

        <div class="user-detail-header">
            <h2><?= e($fullName ?: '—') ?></h2>
            <span class="user-badge <?= $stateClass ?>">
                <?= $stateLabel ?>
            </span>
        </div>

        <div class="user-detail-grid">

            <div>
                <span class="label">ID</span>
                <span class="value"><?= (int)$user['id'] ?></span>
            </div>

            <div>
                <span class="label">Email</span>
                <span class="value"><?= e($user['email']) ?></span>
            </div>

            <div>
                <span class="label">Osobní číslo</span>
                <span class="value"><?= e($user['employee_number']) ?></span>
            </div>

            <div>
                <span class="label">Role</span>
                <span class="value"><?= e($user['global_role']) ?></span>
            </div>

            <div>
                <span class="label">Vytvořen</span>
                <span class="value"><?= e($user['created_at']) ?></span>
            </div>

        </div>
    </div>

    <!-- ACTIONS -->

<div class="user-detail-card">

    <h3>Akce</h3>

    <div class="user-detail-actions">

        <?php if ($user['password_hash'] === null): ?>
            <form method="post"
                  action="<?= Url::to('/{tenant}/users/' . (int)$user['id'] . '/resend-activation') ?>">
                <?= Csrf::getField() ?>
                <button type="submit" class="btn-primary">
                    Poslat aktivační e-mail
                </button>
            </form>
        <?php endif; ?>

        <a href="<?= Url::to('/{tenant}/users') ?>/#main"
           class="btn-secondary">
            ← Zpět na výpis uživatelů
        </a>

    </div>

</div>

</div>

<?php else: ?>

<div class="ui-alert ui-alert-error">
    Uživatel nebyl nalezen.
</div>

<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
