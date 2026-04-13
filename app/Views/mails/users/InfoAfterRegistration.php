<?php
declare(strict_types=1);

/** @var string $user */
/** @var string $companyName */
/** @var string $ico */
/** @var string $email */
/** @var string $loginLink */


?>

<p>Dobrý den, <?= $user ?>,</p>

<p>vaše firma "<strong><?= $companyName ?></strong>" byla úspěšně vytvořena.</p>

<p>
Přihlašovací údaje:<br>
IČO: <?= $ico ?><br>
Email: <?= $email ?>
</p>

<p>
➡️ Přihlásit se můžete zde:<br>
<?= $loginLink ?>
</p>

<p>
V systému je již vytvořena první zakázka a několik úkolů k ní.<br>
To Vám pomůže seznámit se s funkcemi Bó systému.
</p>
<p>
Co můžete udělat dále:
</p>
<ul>
<li>vytvářet zakázky</li>
<li>přidat kolegy</li>
<li>přidat úkoly k zakázkám</li>
<li>psát reporty k úkolům</li>
</ul>
<p>
Pokud jste tuto registraci neprovedli, kontaktujte nás.
</p>

<p>

</p>

