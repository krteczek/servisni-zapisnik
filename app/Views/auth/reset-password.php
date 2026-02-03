<?php
use App\Core\Url;
use App\Core\Csrf;
require __DIR__ . '/../layout/header.php';
$token = $view->token ?? '';
$errors = $view->errors ?? [];
?>
<h1>Nastavení nového hesla</h1>
<?php if ($errors): ?>
<ul class="errors">
    <?php foreach ($errors as $field => $messages): ?>
        <?php foreach ((array) $messages as $message): ?>
            <li><?= e($message) ?></li>
        <?php endforeach ?>
    <?php endforeach ?>
</ul>
<?php endif ?>

<form method="post" action="">
    <?= Csrf::getField() ?>

    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

    <div>
        <label>Nové heslo</label>
        <input type="password" name="password" required>
    </div>
    <div>
        <label>Nové heslo znovu</label>
        <input type="password" name="passwordZ" required>
    </div>

    <button type="submit">Změnit heslo</button>
</form>



<?php require __DIR__ . '/../layout/footer.php'; ?>        <label>Nové heslo</label>
        <input type="password" name="password" required>
