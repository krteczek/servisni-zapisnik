<?php
declare(strict_types=1);

require __DIR__ . '/../layout/header.php';

use App\Core\Url;
use App\Core\Csrf;

$old = $view->old ?? [];
$errors = $view->errors ?? [];
?>


<form method="post" action="<?= Url::to('/users/' . $view->old['id'] . '/edit') ?>">
<?= Csrf::getField() ?>

<label>
Email<br>
<input name="email" value="<?= htmlspecialchars($view->old['email']) ?>"><br><br>
</label>

<label>
Číslo zaměstnance<br>
<input name="employee_number" value="<?= htmlspecialchars($view->old['employee_number']) ?>"><br><br>
</label>

<label>
Jméno<br>
<input name="first_name" value="<?= htmlspecialchars($view->old['first_name']) ?>"><br><br>
</label>

<label>
Příjmení<br>
<input name="last_name" value="<?= htmlspecialchars($view->old['last_name']) ?>"><br><br>
</label>

<label>
Role<br>
<select name="global_role">
<?php foreach ($view->roles as $key => $label): ?>
<option value="<?= $key ?>" <?= $key === $view->old['global_role'] ? 'selected' : '' ?>>
<?= $label ?>
</option>
<?php endforeach; ?>
</select>
</label><br><br>

<label>
<input type="checkbox" name="active" <?= $view->old['active'] ? 'checked' : '' ?>>
 Aktivní účet
</label><br><br>

<button type="submit">Uložit změny</button>
</form>
