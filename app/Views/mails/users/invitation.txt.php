<?php
declare(strict_types=1);

/** @var string $activationUrl */
/** @var string $companyName */
/** @var int $expiresMinutes */
/** @var string $title */


?>

<?= $title ?>
**********************

Dobrý den,

společnost <?= $companyName ?> sro Vám vytvořila účet v Bó systému.

Pro dokončení registrace klikněte na odkaz níže:

<?= $activationUrl ?>


Platnost aktivačního odkazu je <?= $expiresMinutes ?> dní.

Pokud jste o vytvoření účtu nevěděli, kontaktujte prosím administrátora společnosti <?= $companyName ?> nebo zprávu ignorujte.

——————————————————————————————————————————————————————————————
Tým Bó systém
