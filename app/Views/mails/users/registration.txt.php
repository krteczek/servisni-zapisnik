<?php
declare(strict_types=1);

/** @var string $activationUrl */
/** @var int $expiresMinutes */
/** @var string $title */

?>

<!-- views/emails/user/registration.php -->

<?= $title ?>
--------------------------

Pro dokončení registrace do Bó systému klikněte na odkaz níže pro aktivaci účtu:
            
<?= $activationUrl ?>
            

Platnost odkazu je <?= $expiresMinutes ?>.

Pokud tato zpráva není určená Vám, tak ji, prosím, ignorujte.

——————————————————————————————————————————————————————————————
Tým Bó systém

<!-- Registration -->