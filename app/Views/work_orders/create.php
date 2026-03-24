<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

require __DIR__ . '/../layout/header.php';

use App\Core\Url;
use App\Core\Csrf;
use App\Core\Access;
$data = $view->data; 
?>

<h1>Nový požadavek</h1>

<div class="create-container">

    <!-- 🔹 FORM -->
    <div class="card">
        <div class="card-body">

            <?php require __DIR__ . '/../layout/formsErrors.php'; ?>

            <form method="post">
                <?= Csrf::getField() ?>

                <!-- Externí číslo -->
                <div class="form-group">
                    <label>Externí číslo</label>
                    <input
                        class="form-control"
                        name="external_number"
                        value="<?= e($data['external_number'] ?? '') ?>"
                    >
                </div>
                <div class="form-group">
                    <label>Interní číslo</label>
                <?= 'WO-' . str_pad($data['internal_number'], 5, '0', STR_PAD_LEFT) ?>
                </div>
                <!-- Název -->
                <div class="form-group">
                    <label>Název <span class="req">*</span></label>
                    <input
                        class="form-control"
                        name="title"
                        required
                        value="<?= e($data['title'] ?? '') ?>"
                    >
                </div>

                <!-- Popis -->
                <div class="form-group">
                    <label>Popis</label>
                    <textarea
                        class="form-control"
                        name="description"
                        rows="4"
                    ><?= e($data['description'] ?? '') ?></textarea>
                </div>

                <!-- Zdroj -->
                <div class="form-group">
                    <label>Zdroj</label>
                    <select class="form-control" name="source">
                        <option value="email"><?= te('email') ?></option>
                        <option value="phone"><?= te('phone') ?></option>
                        <option value="personal"><?= te('personal') ?></option>
                        <option value="system"><?= te('system') ?></option>
                    </select>
                </div>

                <!-- Požadoval -->
                <div class="form-group">
                    <label>Požadoval</label>
                    <input
                        class="form-control"
                        name="requested_by"
                        value="<?= e($data['requested_by'] ?? '') ?>"
                    >
                </div>

                <!-- Kontakt -->
                <div class="form-group">
                    <label>Kontaktní osoba</label>
                    <input
                        class="form-control"
                        name="contact"
                        value="<?= e($data['contact'] ?? '') ?>"
                    >
                </div>

                <!-- Priorita -->
                <div class="form-group">
                    <label>Priorita</label>
                    <select class="form-control" name="priority">
                        <option value="low">Nízká</option>
                        <option value="normal" selected>Normální</option>
                        <option value="high">Vysoká</option>
                        <option value="emergency">Havárie</option>
                    </select>
                </div>

                <!-- ACTIONS -->
                <div class="form-actions">
                    <button class="btn btn-primary">Uložit</button>
                    <a href="<?= Url::to('/{tenant}/work-orders') ?>/#main" class="btn btn-secondary">
                        Zpět na přehled
                    </a>
                </div>

            </form>

        </div>
    </div>

    <!-- 🔹 HELP (klidně později) -->
    <div class="card card-help">
        <div class="card-body">
            <h3>Tip</h3>
            <p>
                Vyplňte co nejvíce informací – usnadní to zpracování požadavku.
            </p>
        </div>
    </div>

</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
