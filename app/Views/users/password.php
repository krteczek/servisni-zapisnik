<?php
declare(strict_types=1);

// views/users/password.php

use App\Core\Url;
use App\Core\Csrf;

require __DIR__ . '/../layout/header.php';

$user   = $view->data;
$errors = $view->errors ?? [];
?>

<h2>Změna hesla uživatele:
    <?= htmlspecialchars($user['first_name']) ?>
    <?= htmlspecialchars($user['last_name']) ?>
</h2>

<?php if ($errors): ?>
<ul style="color:red">
    <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
    <?php endforeach ?>
</ul>
<?php endif ?>

<form method="post"
      action="<?= Url::to('/users/' . $user['id'] . '/password') ?>"
      autocomplete="off"
      data-lpignore="true">

    <?= Csrf::getField() ?>

    <!-- FALEŠNÉ POLE – uklidní Chrome -->
    <input type="text"
           name="fake_user"
           autocomplete="username"
           style="display:none">

    <!-- FALEŠNÉ HESLO – kritické -->
    <input type="password"
           name="fake_pass"
           autocomplete="current-password"
           style="display:none">

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
