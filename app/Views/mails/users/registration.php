<?php
declare(strict_types=1);

/** @var string $activationUrl */
/** @var int $expiresMinutes */


?>

<!-- views/emails/user/registration.php -->
            
            <p>Pro dokončení registrace do Bó systému klikněte na tlačítko níže pro aktivaci účtu:</p>
            <p>
                <a href="<?= $activationUrl ?>" 
                   style="padding:12px 20px;background:#2d6cdf;color:#fff;text-decoration:none;border-radius:6px;">
                   Aktivovat účet
                </a>
            </p>
            <p>Platnost odkazu je <?= $expiresMinutes ?>.</p>
            <p style="font-size:12px;color:#999;">
Pokud tlačítko nefunguje, použijte tento odkaz:<br>
<?= $activationUrl ?>
</p>
            <p>Pokud tato zpráva není určená Vám, tak ji, prosím, ignorujte.</p>
<!-- Registration -->