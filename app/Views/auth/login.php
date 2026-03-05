<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */


use App\Core\Url;
use App\Core\Csrf;

require __DIR__ . '/../layout/header.php';
?>
<p>Zadejte údaje pro přístup do Vašeho pracovního prostoru.</p>
<p>
  Nemáte ještě účet?
  <a href="<?= Url::to('/register') ?>">Vytvořte si účet a pracovní prostor</a>
</p>

<form method="post" action="<?= Url::current() ?>">
    <?= Csrf::getField() ?>

    <?php if (!empty($view->errors['_csrf'])): ?>
        <div class="error"><?= e($view->errors['_csrf'][0]) ?></div>
    <?php endif; ?>

    <?php if (!empty($view->errors['global'])): ?>
        <div class="error"><?= e($view->errors['global'][0]) ?></div>
    <?php endif; ?>
<table>
<tr>
	<td>
    Pracovní prostor
     <?php if (!empty($view->errors['tenant'])): ?>
        <div class="error"><?= e($view->errors['tenant'][0]) ?></div>
    <?php endif; ?>

	</td>
	<td><input
    name="tenant"
    placeholder="např. servis-novak"
    value="<?= e($view->data['tenant'] ?? '') ?>" title="Název, který jste zvolili při vytvoření pracovního prostoru." 
>

	</td>
</tr>
    
<tr>
	<td>
    Email
        <?php if (!empty($view->errors['email'])): ?>
            <div class="error"><?= e($view->errors['email'][0]) ?></div>
        <?php endif; ?>

	</td>
	<td>
		<input name="email" value="<?= e($view->data['email'] ?? '') ?>">
	</td>
</tr>

<tr>
	<td>
    Heslo
        <?php if (!empty($view->errors['password'])): ?>
            <div class="error"><?= e($view->errors['password'][0]) ?></div>
        <?php endif; ?>
	</td>
	<td>
		<input type="password" name="password">
	</td>
</tr>
<tr>
<td colspan="2"><button>Přihlásit</button></td>
</tr>
</table>
    
</form>
<p>Nemůžete se přihlásit? <a href="<?= Url::to('/forgot-password') ?>">Zapomněli jste heslo?</a></p>


<?php require __DIR__ . '/../layout/footer.php'; ?>
