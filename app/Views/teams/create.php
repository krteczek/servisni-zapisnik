<?php
declare(strict_types=1);

// views/teams/create.php – vytvoření týmu

use App\Core\Url;
use App\Core\Csrf;

require __DIR__ . '/../layout/header.php';
?>

<?= $css ?>
<?php if (!empty($view->error)): ?>
    <p style="color:red"><?= htmlspecialchars($view->error) ?></p>
<?php endif; ?>

<form method="post" action="">
<?= Csrf::getField() ?>
    <label>Název týmu</label><br>
    <input type="text" name="name"><br><br>

    <label>Barva</label><br>
    <input type="color" name="color" value="#2196F3"><br><br>

    <button type="submit">Vytvořit</button>
</form>
