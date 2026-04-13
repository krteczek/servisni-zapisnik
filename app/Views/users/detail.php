<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
use App\Core\Csrf;


require __DIR__ . '/../layout/header.php';

$user = $view->user ?? [];

//print_r($user);


if (!empty($user)): ?>

<?php
$fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

$active = active($user);
?>

<!-- HLAVNÍ KONTEJNER (grid, ale může být i jeden sloupec) -->
<div class="create-container">

    <!-- LEVÝ/VELKÝ SLOUPEC – DETAIL -->
    <div class="card card-body">
        

            <!-- HLAVIČKA S NÁZVEM A BADGEM -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h2 style="margin:0;"><?= e($fullName ?: '—') ?></h2>
                <span class="badge badge-<?= e($active) ?>">
                    <?= te($active) ?>
                </span>
            </div>

            <!-- DETAILOVÝ GRID (2 sloupce) -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px 30px;">

                <div>
                    <div style="font-size:0.85rem; color:#4b5563; margin-bottom:4px;">Email</div>
                    <div style="font-size:1.1rem; font-weight:500;"><?= e($user['email']) ?></div>
                </div>

                <div>
                    <div style="font-size:0.85rem; color:#4b5563; margin-bottom:4px;">Osobní číslo</div>
                    <div style="font-size:1.1rem; font-weight:500;"><?= e($user['employee_number']) ?></div>
                </div>
               <div>
                    <div style="font-size:0.85rem; color:#4b5563; margin-bottom:4px;">telefon</div>
                    <div style="font-size:1.1rem; font-weight:500;"><?= e($user['telefon']) ?></div>
                </div>

                <div>
                    <div style="font-size:0.85rem; color:#4b5563; margin-bottom:4px;">Role</div>
                    <div style="font-size:1.1rem; font-weight:500;"><?= te($user['global_role']) ?></div>
                </div>

                <div>
                    <div style="font-size:0.85rem; color:#4b5563; margin-bottom:4px;">Vytvořen</div>
                    <div style="font-size:1.1rem; font-weight:500;"><?= e($user['created_at']) ?></div>
                </div>

                <?php if (!empty($user['telefon'])): ?>
                <div>
                    <div style="font-size:0.85rem; color:#4b5563; margin-bottom:4px;">Telefon</div>
                    <div style="font-size:1.1rem; font-weight:500;"><?= e($user['telefon']) ?></div>
                </div>
                <?php endif; ?>

            </div>

            <!-- AKCE (tlačítka) - přesunuté dovnitř karty -->
<form method="post"
                          action="<?= Url::to('/{tenant}/users/' . (int)$user['id'] . '/resend-activation') ?>">
            <div class="form-actions">

                <?php if ($user['password_hash'] === null): ?>
                    
                        <?= Csrf::getField() ?>
                        <button type="submit" class="btn btn-primary">
                            Poslat aktivační e-mail
                        </button>
                   
                <?php endif; ?>

                <a href="<?= Url::to('/{tenant}/users/' . (int)$user['id'] . '/edit') ?>/#main"
                   class="btn btn-secondary">
                    Upravit uživatele
                </a>
 
                <a href="<?= Url::to('/{tenant}/users/create') ?>/#main" class="btn btn-secondary">
                    + Nový uživatel
                </a>


                <a href="<?= Url::to('/{tenant}/users') ?>/#main"
                   class="btn btn-secondary">
                    ← Zpět na výpis
                </a>

            </div>
 </form>
        </div>
    
    <!-- PRAVÝ SLOUPEC – NÁPOVĚDA / INFO (volitelně) -->
    <div class="card card-help" id="helpCard">
        <div class="card-body">
            <h3>Detail uživatele</h3>

            <h4>Stav účtu</h4>
            <ul style="padding-left:1.2rem;">
                <li><span class="badge badge-active">Aktivní</span> – může se přihlásit</li>
                <li><span class="badge badge-inactive"><?= te('inactive') ?></span> – nemůže se přihlásit</li>
                <li><span class="badge badge-pending">Čeká na aktivaci</span> – ještě si nenastavil heslo</li>
            </ul>

            <h4>Co dělat dál?</h4>
            <p>
                Můžeš uživatele <strong>upravit</strong>, nebo pokud ještě není aktivovaný,
                <strong>poslat nový aktivační email</strong>.
            </p>

        </div>
    </div>

</div> <!-- /.create-container -->

<?php else: ?>

<div class="ui-alert ui-alert-error">
    Uživatel nebyl nalezen.
</div>

<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>