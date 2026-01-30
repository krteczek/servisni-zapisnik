<?php
use App\Core\Url;

require __DIR__ . '/../layout/header.php';
?>


<form method="post" action="<?= Url::current() ?>">
    <?= $view->csrf ?>

    <?php if (!empty($view->errors['_csrf'])): ?>
        <div class="error"><?= htmlspecialchars($view->errors['_csrf'][0]) ?></div>
    <?php endif; ?>

    <?php if (!empty($view->errors['global'])): ?>
        <div class="error"><?= htmlspecialchars($view->errors['global'][0]) ?></div>
    <?php endif; ?>

<label>
    Firma
    <input name="tenant" value="<?= htmlspecialchars($view->data['tenant'] ?? '') ?>">
    <?php if (!empty($view->errors['tenant'])): ?>
        <div class="error"><?= htmlspecialchars($view->errors['tenant'][0]) ?></div>
    <?php endif; ?>
</label>
<br>

    <label>
        Email
        <input name="email" value="<?= htmlspecialchars($view->data['email'] ?? '') ?>">
        <?php if (!empty($view->errors['email'])): ?>
            <div class="error"><?= htmlspecialchars($view->errors['email'][0]) ?></div>
        <?php endif; ?>
    </label>
<br>
    <label>
        Heslo
        <input type="password" name="password">
        <?php if (!empty($view->errors['password'])): ?>
            <div class="error"><?= htmlspecialchars($view->errors['password'][0]) ?></div>
        <?php endif; ?>
    </label>
<br>
    <button>Přihlásit</button>
</form>

<?php require __DIR__ . '/../layout/footer.php'; ?>
