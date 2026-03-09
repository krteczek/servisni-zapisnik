<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */


use App\Core\Url;
use App\Core\Csrf;
require __DIR__ . '/../layout/header.php';
$token = $view->data['token'] ?? '';
$title = $view->data['button'] ?? '';
$errors = $view->errors ?? [];

$ch = '';
if ($errors) {
    foreach ($errors as $field => $messages) {
        foreach ($messages as $msg) {
            ?> <p><?= e($msg) ?></p><?php
        }
    }
}	
?>

<?= e($ch) ?>

<form method="post" action="">
    <?= Csrf::getField() ?>

    <input type="hidden" name="token" value="<?= e($token) ?>">

    <div>
        <label>Nové heslo</label>
        <input type="password" name="password" required>
    </div>
    <div>
        <label>Nové heslo znovu</label>
        <input type="password" name="passwordZ" required>
    </div>

    <button type="submit"><?= $title ?></button>
</form>



<?php require __DIR__ . '/../layout/footer.php'; ?>
