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

            <p>Zadejte přihlašovací údaje do Vašeho firemního účtu.</p>

            <form method="post">
                <?= Csrf::getField() ?>

                <?php require __DIR__ . '/../layout/formsErrors.php'; ?>

                <!-- TENANT -->
                <div class="form-group <?= !empty($view->errors['tenant']) ? 'has-error' : '' ?>">
                    <label for="tenant">IČO Vaší firmy</label>
                    <input
                        class="form-control"
                        name="tenant"
                        id="tenant"
                        inputmode="numeric"
                        placeholder="IČO Vaší firmy"
                        autocomplete="organization-number"
                        pattern="\d{8}"
                        title="IČO musí mít 8 číslic"
                        value="<?= e($view->data['tenant'] ?? '') ?>"
                        required>

                    <?php if (!empty($view->errors['tenant'])): ?>
                        <div class="error-message"><?= e($view->errors['tenant'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- EMAIL -->
                <div class="form-group <?= !empty($view->errors['email']) ? 'has-error' : '' ?>">
                    <label for="email">Email</label>
                    <input
                        class="form-control"
                        name="email"
                        id="email"
                        type="email"
                        autocomplete="username"
                        value="<?= e($view->data['email'] ?? '') ?>"
                        required>

                    <?php if (!empty($view->errors['email'])): ?>
                        <div class="error-message"><?= e($view->errors['email'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- PASSWORD -->
                <div class="form-group <?= !empty($view->errors['password']) ? 'has-error' : '' ?>">
                    <label for="password">Heslo</label>
                    <input
                        type="password"
                        class="form-control"
                        autocomplete="current-password"
                        name="password"
                        id="password"
                        required
                    >

                    <?php if (!empty($view->errors['password'])): ?>
                        <div class="error-message"><?= e($view->errors['password'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary">Přihlásit</button>
                </div>

            </form>

            <p style="margin-top:16px;">
                <a href="<?= Url::to('/forgot-password') ?>" class="link">
                    Zapomněli jste heslo?
                </a>
            </p>
				<p style="margin-top:20px; font-size: 13px; color: #666;">
				    Tento web používá pouze nezbytné cookies pro přihlášení a bezpečný provoz aplikace.
				    <a href="<?= Url::to('/pages/cookies') ?>" class="link">Více informací o cookies zde</a>.
				</p>
        </div>
    </div>


    <!-- 🔹 PRAVÁ STRANA – HELP -->
    <div class="card card-help">
        <div class="card-body">

            <h3>Nápověda:</h3>
            
            <h4>Nemáte účet?</h4>
            <p>
                Vytvořte si firemní účet a získejte pracovní prostor, 
                ve kterém můžete evidovat a zpracovávat  Vaše zakázky a úkoly k nim.
            </p>

            <p>
                <a href="<?= Url::to('/register') ?>" class="link"><strong> ➜ Vytvořit firemní účet</strong></a>
            </p>
            <p>
               <strong>Poznámka: </strong>
               Účty pro spolupracovníky a zaměstnance                            
                se vytvářejí až po založení firemního účtu, a to v nastavení
                Vašeho firemního pracovního prostoru.
            </p>

            <hr>

            <h4>Přihlášení</h4>
            
                <p>K přihlášení potřebujete:</p>
                <ul>
                    <li>IČO Vaší firmy</li>
                    <li>email</li>
                    <li>heslo</li>
                </ul>
          
            <hr>

            <h4>Zapomněli jste heslo?</h4>
            <p>
                Máte již zde účet, ale zapomněli jste přístupové heslo?
                Žádný problém! Na této stránce můžete <a href="<?= Url::to('/forgot-password') ?>"
                title="Požádat o nové heslo do Bó systému" class="link"><strong>požádat o nové heslo</strong></a> do Bó systému.

            </p>

        </div>
    </div>

</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>