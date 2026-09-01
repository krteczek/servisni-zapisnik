<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
use App\Core\Csrf;

require __DIR__ . '/../layout/header.php';
?>

<div class="create-container">

    <!-- 🔹 LEVÁ STRANA – FORM -->
    <div class="card">
        <div class="card-body">
            <p class="ui-alert ui-alert-warning">
                BETA VERZE - TESTOVACÍ REŽIM<br>
                Systém běží v testovacím režimu.
                Rozsah funkcí a přístupů může být 
                omezený, ale základní funkčnost je 
                zachována. Děkujeme za pochopení.
                Případné chyby hlaste na 
                <a href="mailto:krteczek01@gmail.cz" class="link">
                    krteczek01@gmail.cz
                </a>
            </p>

            <p>Vyberte roli uživatele, bude vám přiřazen existující účet.</p>
            <ul>

                <li><a href="<?= Url::to('/dev-login-set/root') ?>">Root</a></li>
                <li><a href="<?= Url::to('/dev-login-set/admin') ?>">Admin</a></li>
                <li><a href="<?= Url::to('/dev-login-set/mistr') ?>">Mistr</a></li>
                <li><a href="<?= Url::to('/dev-login-set/predak') ?>">Předák</a></li>
                <li><a href="<?= Url::to('/dev-login-set/monter') ?>">Montér</a></li>
            </ul>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>