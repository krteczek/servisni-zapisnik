<?php
declare(strict_types=1);

/** @var string $activationUrl */
/** @var string $companyName */
/** @var int $expiresMinutes */

use App\Core\Url;
?>
<!-- views/emails/user/invitation.php -->

            <p style="color:#555555; line-height:1.6;">
              Dobrý den,
            </p>

            <p style="color:#555555; line-height:1.6;">
              společnost <strong><?= $companyName ?></strong> Vám vytvořila účet v Bó systému.
            </p>

            <p style="color:#555555; line-height:1.6;">
              Pro dokončení registrace klikněte na tlačítko níže:
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
                Aktivovat účet
              </a>
            </p>

            <p style="color:#777777; font-size:14px; line-height:1.6;">
              Platnost aktivačního odkazu je <?= $expiresMinutes ?>.
            </p>
            <p style="font-size:12px;color:#999;">
Pokud tlačítko nefunguje, použijte tento odkaz:<br>
<?= $activationUrl ?>
</p>

            <hr style="border:none; border-top:1px solid #eeeeee; margin:30px 0;">

            <p style="color:#999999; font-size:13px; line-height:1.6;">
              Pokud jste o vytvoření účtu nevěděli, kontaktujte prosím administrátora
              společnosti <?= $companyName ?>
              nebo zprávu ignorujte.
            </p>

<!-- Invitation --> 