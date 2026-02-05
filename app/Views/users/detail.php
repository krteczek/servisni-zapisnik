<?php
declare(strict_types=1);

use App\Core\Url;
use App\Core\Roles;
use App\Core\Csrf;

require __DIR__ . '/../layout/header.php';

$user   = $view->user ?? [];
$errors = $view->errors ?? [];
$accountState = $view->accountState ?? [];

?>
<?php if (!empty($user)): ?>
<dl class="dl">
<dt>ID</dt>
<dd><?= (int) $user['id'] ?></dd>


<dt>Email</dt>
<dd><?= htmlspecialchars($user['email']) ?></dd>


<dt>Jméno</dt>
<dd><?= htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></dd>


<dt>Osobní číslo</dt>
<dd><?= htmlspecialchars($user['employee_number']) ?></dd>


<dt>Role</dt>
<dd><?= htmlspecialchars($user['global_role']) ?></dd>


<dt>Stav účtu</dt>
<dd>
<?php if ($user['password_hash'] === null): ?>
<span class="badge badge-warning">Čeká na aktivaci</span>
<?php elseif ((int) $user['active'] === 1): ?>
<span class="badge badge-success">Aktivní</span>
<?php else: ?>
<span class="badge badge-danger">Deaktivovaný</span>
<?php endif; ?>
</dd>


<dt>Vytvořen</dt>
<dd><?= htmlspecialchars($user['created_at']) ?></dd>
</dl>
</section>


<section class="card">
<h2>Akce</h2>


<?php if ($user['password_hash'] === null): ?>
<!-- resend activation -->
<form method="post" action="<?= Url::to('/users/' . (int) $user['id'] . '/resend-activation') ?>">
<?= Csrf::getField() ?>
<button class="btn btn-primary">
Poslat aktivační e-mail
</button>
</form>


<?php else: ?>
<?php endif; ?>


<a href="<?= Url::to('/users') ?>/#main" class="btn btn-link">← zpět na seznam</a>
</section>


<?php else: ?>
<p>Uživatel nebyl nalezen.</p>
<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>

