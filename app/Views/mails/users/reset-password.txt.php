<?php
declare(strict_types=1);

/** @var string $activationUrl */
/** @var string $companyName */
/** @var string $expiresMinutes */
/** @var string $title */
use App\Core\Url;
?>

<?= $title ?>
----------------------

Dobrý den,

společnost "<?= $companyName ?>" Vám vytvořila účet v systému Bó.

Pro dokončení registrace klikněte na odkaz níže:

<?= $activationUrl ?>

Platnost aktivačního odkazu je <?= $expiresMinutes ?> dní.

Pokud jste o vytvoření účtu nevěděli, kontaktujte prosím administrátora společnosti <?= $companyName ?> nebo zprávu ignorujte.

——————————————————————————————————————————————————————————————
Tým Bó systém
