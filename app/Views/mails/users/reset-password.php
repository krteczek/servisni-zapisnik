<?php
declare(strict_types=1);

/** @var string $activationUrl */
/** @var string $companyName */
/** @var string $expiresMinutes */

use App\Core\Url;
?>


<h2 style="margin-top:0; color:#333333;">
Změna hesla
</h2>

<p style="color:#555555; line-height:1.6;">
Dobrý den,
</p>

<p style="color:#555555; line-height:1.6;">
byla podána žádost o změnu hesla k Vašemu účtu v systému <strong>Bó</strong>
u společnosti <strong><?= $companyName ?></strong>.
</p>

<p style="color:#555555; line-height:1.6;">
Pro nastavení nového hesla klikněte na tlačítko níže:
</p>

<p style="text-align:center; margin:30px 0;">
<a href="<?= $activationUrl ?>"
   style="background-color:#2f6fed;
          color:#ffffff;
          text-decoration:none;
          padding:14px 28px;
          border-radius:6px;
          display:inline-block;
          font-weight:bold;">
Nastavit nové heslo
</a>
</p>

<p style="color:#777777; font-size:14px; line-height:1.6;">
Platnost odkazu je <?= $expiresMinutes ?>.
</p>
            <p style="font-size:12px;color:#999;">
Pokud tlačítko nefunguje, použijte tento odkaz:<br>
<?= $activationUrl ?>
</p>

<hr style="border:none; border-top:1px solid #eeeeee; margin:30px 0;">

<p style="color:#999999; font-size:13px; line-height:1.6;">
Pokud jste o změnu hesla nežádali, tuto zprávu ignorujte.
Vaše heslo zůstane beze změny.
</p>
