<?php require __DIR__ . '/../layout/header.php'; ?>

<h1>Přihlášení do aplikace Servisní Zápisník</h1>
<form method="post" action="./login">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($view->csrf) ?>">

    <?php if (!empty($view->errors['global'])): ?>
        <div class="error"><?= htmlspecialchars($view->errors['global'][0]) ?></div>
    <?php endif; ?>

    <label>
        Email
        <input name="email" value="<?= htmlspecialchars($view->data['email'] ?? '') ?>"><br>
        <?php if (!empty($view->errors['email'])): ?>
            <div class="error"><?= htmlspecialchars($view->errors['email'][0]) ?></div>
        <?php endif; ?>
    </label>

    <label>
        Heslo
        <input type="password" name="password">
        <?php if (!empty($view->errors['password'])): ?><br>
            <div class="error"><?= htmlspecialchars($view->errors['password'][0]) ?></div><br>
        <?php endif; ?>
    </label>

    <br><button>Přihlásit</button>
</form>

<?php require __DIR__ . '/../layout/footer.php'; ?>
