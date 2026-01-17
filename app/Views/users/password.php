<?php
declare(strict_types=1);

// views/users/password.php

use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$user   = $view->data ?? [];
$errors = $view->errors ?? [];
?>

<h2>Změna hesla uživatele:
    <?= htmlspecialchars($user['first_name'] ?? '') ?>
    <?= htmlspecialchars($user['last_name'] ?? '') ?>
</h2>

<?php if ($errors): ?>
<ul class="errors">
    <?php foreach ($errors as $field => $messages): ?>
        <?php foreach ((array) $messages as $message): ?>
            <li><?= htmlspecialchars($message) ?></li>
        <?php endforeach ?>
    <?php endforeach ?>
</ul>
<?php endif ?>

<form method="post"
      action="<?= Url::to('/users/' . ($user['id'] ?? '') . '/password') ?>"
      autocomplete="off"
      data-lpignore="true">

    <?= $this->csrfField() ?>

    <!-- fake username (password managery) -->
    <input type="text"
           name="fake_user"
           autocomplete="username"
           hidden>

    <!-- fake current password -->
    <input type="password"
           name="fake_pass"
           autocomplete="current-password"
           hidden>

    <label>Nové heslo</label><br>
    <input type="password"
           name="new_password"
           autocomplete="new-password"
           data-lpignore="true"><br><br>

    <label>Potvrzení hesla</label><br>
    <input type="password"
           name="new_password_confirm"
           autocomplete="new-password"
           data-lpignore="true"><br><br>

    <button type="submit">Změnit heslo</button>
</form>

<p>
    <a href="<?= Url::to('/users') ?>" class="btn btn-secondary">
        ← Zpět na přehled
    </a>
</p>
<?php require __DIR__ . '/../layout/footer.php'; ?>