<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Csrf;
use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$err = $view->errors;
?>

<div class="create-container">

    <!-- 🔹 FORM -->
    <div class="card">
        <div class="card-body">

            <p>
                Zadejte email a název pracovního prostoru (nebo IČO Vaší firmy), ke kterému máte přístup.
                Pošleme Vám odkaz pro nastavení nového hesla.
            </p>

            <?php require __DIR__ . '/../layout/formsErrors.php'; ?>

            <form method="post">

                <?= Csrf::getField() ?>

                <!-- EMAIL -->
                <div class="form-group <?= !empty($err['email']) ? 'has-error' : '' ?>">
                    <label>Email</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="<?= e($view->data['email'] ?? '') ?>"
                        required
                    >

                    <?php if (!empty($err['email'])): ?>
                        <div class="error-message"><?= e($err['email'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- TENANT -->
                <div class="form-group <?= !empty($err['tenant']) ? 'has-error' : '' ?>">
                    <label>Pracovní prostor</label>
                    <input
                        type="text"
                        name="tenant"
                        class="form-control"
                        placeholder="např. servis-novak nebo IČO"
                        value="<?= e($view->data['tenant'] ?? '') ?>"
                        required
                    >

                    <?php if (!empty($err['tenant'])): ?>
                        <div class="error-message"><?= e($err['tenant'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary">
                        Odeslat odkaz
                    </button>
                </div>

            </form>

            <p style="margin-top:16px;">
                <a href="<?= Url::to('/login') ?>" class="link">
                    ← Zpět na přihlášení
                </a>
            </p>

        </div>
    </div>


    <!-- 🔹 HELP -->
    <div class="card card-help">
        <div class="card-body">

            <h3>Jak to funguje?</h3>
            <ul>
            	<li>Zadáte Email</li>
            	<li>Zadáte Pracovní prostor</li>
            </ul>
            <p>
                Pokud existuje účet v tomto pracovním prostoru a s tímto emailem,
                zašleme Vám odkaz pro nastavení nového hesla.
            </p>

            <div class="ui-alert ui-alert-warning">
                Odkaz má omezenou platnost.
            </div>

            <hr>

            <h4>Neznáte pracovní prostor?</h4>
            <p>
                Použijte název, který jste zadali při registraci
                (např. <strong>servis-novak</strong>) nebo IČO firmy.
            </p>

        </div>
    </div>

</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>