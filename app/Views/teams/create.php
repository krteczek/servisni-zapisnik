<?php
declare(strict_types=1);

// views/teams/create.php – vytvoření týmu

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
use App\Core\Csrf;


require __DIR__ . '/../layout/header.php';

$old    = $view->data ?? [];
$errors = $view->errors ?? [];
?>

<!-- HLAVNÍ KONTEJNER (grid 2:1) -->
<div class="create-container">

    <!-- LEVÝ SLOUPEC – FORMULÁŘ -->
    <div class="card">
        <!-- INFO ALERT (stejný koncept jako u user) -->
        <div class="ui-alert ui-alert-info">
            <strong>Informace:</strong>
            Tým slouží pro sdružení pracovníků (montérů, předáků) do pracovních part.
            Každý tým může mít přiřazené úkoly a zakázky.
        </div>

<?php require __DIR__ . '/../layout/formsErrors.php'; ?>

        <div class="card-body">
			<div class="form-container">
            <form method="post" action="">
                <?= Csrf::getField() ?>

                <!-- NÁZEV TÝMU -->
                <div class="form-group <?= isset($errors['name']) ? 'has-error' : '' ?>">
                    <label for="name">Název týmu <span class="req">*</span></label>
                    <input type="text"
                           id="name"
                           name="name"
                           class="form-control"
                           value="<?= e($old['name'] ?? '') ?>"
                           placeholder="např. Montéři východ"
                           required>
                    <?php if (isset($errors['name'])): ?>
                        <span class="error-message"><?= e($errors['name']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- BARVA TÝMU (speciální input) -->
					<div class="form-inline">
					    <input type="color"
					           id="color"
					           name="color"
					           value="<?= e($old['color'] ?? '#2196F3') ?>">
					    <span class="meta-label-header">
					        (vyber barvu pro označení týmů)
					    </span>
					</dniv>
					<dinv>
                    <?php if (isset($errors['color'])): ?>
                        <span class="error-message"><?= e($errors['color']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- FORMULÁŘOVÁ TLAČÍTKA -->
                <div class="form-actions" style="margin-top: 32px;"><?= Csrf::getField() ?>
                    <button type="submit" class="btn btn-primary">
                        Vytvořit tým
                    </button>

                    <a href="<?= Url::to('/{tenant}/teams') ?>/#main"
                       class="btn btn-secondary">
                        <span class="btn-icon">←</span>
                        Zpět na přehled týmů
                    </a>
                </div>

            </form>
        </div>
       </div>
    </div>
    <!-- PRAVÝ SLOUPEC – NÁPOVĚDA -->
    <div class="card card-help" id="helpCard">
        <div class="card-body">
            <h3>O týmech</h3>

            <h4>K čemu jsou týmy?</h4>
            <p>
                Týmy sdružují pracovníky (montéry, předáky) do pracovních part.
                Můžeš jim pak hromadně přiřazovat úkoly nebo zakázky.
            </p>

            <h4>Barva týmu</h4>
            <p>
                Barva se zobrazuje v přehledech a kalendáři – usnadňuje orientaci,
                který tým má co na starosti.
            </p>

            <h4>Tip</h4>
            <p>
                Pokud týmy barevně odlišíš (např. červená – elektro, modrá – voda,
                zelená – stavba), bude systém přehlednější.
            </p>

            <hr style="margin:20px 0; border:0; border-top:1px solid #e5e7eb;">

            <h4>Filozofie projektu</h4>
            <details>
                <summary style="cursor:pointer; font-weight:600;">Zobrazit</summary>
                <p style="margin-top:12px;">
                    Zakázka je Bůh. Aby se Bůh mohl realizovat, zažít, naplnit,
                    sestoupil k nám a rozpadl se na jednotlivé úkoly.
                    Skrze splnění těchto úkolů (reporty o vykonané práci),
                    se Bůh, čili zakázka realizuje. Aby se úkoly mohly splnit,
                    je potřeba (ještě stále) lidi – a ti pracují v týmech. 
                    Každý fotbalový tým má své barvy, buďt skromnější, stačí Vám jedna barva na tým.
                </p>
            </details>
        </div>
    </div>

</div> <!-- /.create-container -->

<?php require __DIR__ . '/../layout/footer.php'; ?>