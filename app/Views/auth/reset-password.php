<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Csrf;

require __DIR__ . '/../layout/header.php';

$errors = $view->errors;
$token  = $view->data['token'] ?? '';
?>

<div class="create-container">

    <div class="card">
        <div class="card-body">

            <?php require __DIR__ . '/../layout/formsErrors.php'; ?>

            <form method="post">

                <?= Csrf::getField() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">

                <!-- PASSWORD -->
                <div class="form-group <?= ($errors['password'] ?? false) ? 'has-error' : '' ?>">
                    <label>Nové heslo</label>
                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        required
                    >

                    <?php if ($errors['password'] ?? false): ?>
                        <div class="error-message"><?= e($errors['password'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <!-- PASSWORD AGAIN -->
                <div class="form-group <?= ($errors['passwordZ'] ?? false) ? 'has-error' : '' ?>">
                    <label>Nové heslo znovu</label>
                    <input
                        type="password"
                        name="passwordZ"
                        class="form-control"
                        required
                    >

                    <?php if ($errors['passwordZ'] ?? false): ?>
                        <div class="error-message"><?= e($errors['passwordZ'][0]) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary">
                        Nastavit heslo
                    </button>
                </div>

            </form>

        </div>
    </div>
<!-- 🔹 HELP -->
    <div class="card card-help">
        <div class="card-body">

            <h3>Požadavky na heslo</h3>

            <ul>
                <li>alespoň 8 znaků</li>
                <li>doporučeno kombinovat písmena a čísla</li>
            </ul>

            <div class="ui-alert ui-alert-warning">
                Odkaz pro změnu hesla má omezenou platnost.
            </div>

            <hr>

            <h4>Tip</h4>
            <p>
                Pokud změnu hesla nedokončíte včas, budete muset požádat o nový odkaz.
            </p>

        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>