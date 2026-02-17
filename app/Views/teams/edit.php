<?php
declare(strict_types=1);

use App\Core\Url;
use App\Core\Csrf;

$css = '';
$team        = $view->team;
$rolesInTeam = $view->rolesInTeam;

require __DIR__ . '/style.php';
require __DIR__ . '/../layout/header.php';
?>

<?php if (!empty($view->error)): ?>
    <div class="ui-alert ui-alert-danger" style="max-width:1200px; margin:0 auto 20px;">
        <?= htmlspecialchars($view->errors) ?>
    </div>
<?php endif; ?>

<!-- HLAVNÍ KONTEJNER (grid 2:1) -->
<!-- HLAVNÍ KONTEJNER (grid 2:1) -->
<div class="create-container"  style="grid-template-columns: 2fr 1fr;">

    <!-- LEVÝ SLOUPEC – HLAVNÍ OBSAH -->
    <div class="card">
        <div class="card-body">   <!-- ✅ důležité: musí být card-body! -->

            <!-- ÚVOD (jako nápověda) -->
            <div class="ui-alert ui-alert-info" style="margin-bottom:24px;">
                <strong>Nastavení týmu</strong> – uprav název, barvu a spravuj členy.
            </div>

            <!-- ===================== -->
            <!-- ÚPRAVA TÝMU (formulář) -->
            <!-- ===================== -->
            <form method="post" action="/servisni-zapisnik/public/krteczek/teams/11/edit/">
                <input type="hidden" name="_token" value="d2acf954915794057dc40a0d8d92ad50cba709b525c980329517082f947f4427">
                
                <div class="form-group">
                    <label for="name">Název týmu <span class="req">*</span></label>
                    <input type="text"
                           id="name"
                           name="name"
                           class="form-control"
                           value="Admini 1"
                           required>
                </div>

                <div class="form-group">
                    <label for="color">Barva týmu</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="color"
                               id="color"
                               name="color"
                               value="#f66151"
                               style="width: 60px; height: 40px; padding: 2px; border-radius: 6px; border: 1px solid #cfd6de; background: transparent; cursor: pointer;">
                        <span style="color: #4b5563; font-size:0.9rem;">
                            (barva pro označení v přehledech)
                        </span>
                    </div>
                </div>

                <div class="form-actions" style="margin-top:16px;">
                    <button type="submit" class="btn btn-primary">
                        Uložit změny týmu
                    </button>
                </div>
            </form>

            <hr style="margin:32px 0; border:0; border-top:1px solid #e5e7eb;">

            <!-- ===================== -->
            <!-- ČLENOVÉ TÝMU -->
            <!-- ===================== -->
            <h2 style="margin-top:0; margin-bottom:8px;">Členové týmu</h2>
            <p style="color:#4b5563; margin-bottom:24px;">
                Změň roli člena pomocí rozbalovací nabídky.
                Přidání nebo odebrání provedete kliknutím na šipky.
            </p>

            <!-- DUAL LIST – 2 sloupce -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;" id="changelist">

                <!-- V TÝMU -->
                <div>
                    <h3 style="margin-top:0; margin-bottom:16px; font-size:1.1rem;">V týmu</h3>

                    <?php if (empty($view->members)): ?>
                        <div style="padding:20px; background:#f9fafb; border-radius:8px; text-align:center; color:#6b7280;">
                            V tomhle týmu nikdo není
                        </div>
                    <?php endif; ?>

                    <?php foreach ($view->members as $m): ?>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; padding: 8px; background:#f9fafb; border-radius:6px;">
                        <form method="post" action="<?= Url::current() ?>#main" style="margin:0;">
                            <?= Csrf::getField() ?>
                            <input type="hidden" name="change_user_role" value="<?= (int) $m['membership_id'] ?>">
                            <select name="role_in_team" onchange="this.form.submit()" class="form-control" style="width:auto; min-width:100px;">
                                <?php foreach ($rolesInTeam as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= $key === $m['role_in_team'] ? 'selected' : '' ?>>
                                        <?= e($label) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </form>

                        <span style="flex:1; font-weight:500;"><?= e($m['last_name'].' '.$m['first_name']) ?></span>

                        <form method="post" action="<?= Url::current() ?>#main" style="margin:0;">
                            <?= Csrf::getField() ?>
                            <input type="hidden" name="remove_membership_id" value="<?= (int) $m['membership_id'] ?>">
                            <button class="btn btn-secondary" style="padding:4px 10px;" title="Odebrat z týmu">
                                <span style="font-size:1.2rem;">→</span>
                            </button>
                        </form>
                    </div>
                    <?php endforeach ?>
                </div>

                <!-- K DISPOZICI -->
                <div>
                    <h3 style="margin-top:0; margin-bottom:16px; font-size:1.1rem;">K dispozici</h3>

                    <?php if (empty($view->availableUsers)): ?>
                        <div style="padding:20px; background:#f9fafb; border-radius:8px; text-align:center; color:#6b7280;">
                            Žádní další uživatelé
                        </div>
                    <?php endif; ?>

                    <?php foreach ($view->availableUsers as $u): ?>
                    <form method="post" style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; padding: 8px; background:#f9fafb; border-radius:6px;">
                        <?= Csrf::getField() ?>
                        <input type="hidden" name="add_user_id" value="<?= (int)$u['id'] ?>">
                        <input type="hidden" name="role_in_team" value="member">

                        <button class="btn btn-secondary" style="padding:4px 10px;" title="Přidat do týmu">
                            <span style="font-size:1.2rem;">←</span>
                        </button>

                        <span style="flex:1; font-weight:500;">
                            <?= e($u['last_name'].' '.$u['first_name']) ?>
                        </span>
                    </form>
                    <?php endforeach ?>
                </div>
            </div>

            <!-- ZPĚT NA PŘEHLED -->
            <div style="margin-top:32px; padding-top:20px; border-top:1px solid #e5e7eb;">
                <a href="<?= Url::to('/{tenant}/teams') ?>/#main" class="btn btn-secondary">
                    <span class="btn-icon">←</span>
                    Zpět na přehled týmů
                </a>
            </div>

        </div> <!-- /.card-body -->
    </div> <!-- /.card -->

    <!-- PRAVÝ SLOUPEC – NÁPOVĚDA -->
    <div class="card card-help" id="helpCard">
        <div class="card-body">
            <h3>Nápověda k týmům</h3>

            <h4>Úprava týmu</h4>
            <p>
                Můžeš změnit název a barvu týmu. Barva se zobrazuje v přehledech a kalendáři.
            </p>

            <h4>Správa členů</h4>
            <p>
                <strong>V týmu</strong> – zde jsou lidé, kteří už do týmu patří. Můžeš jim změnit roli nebo je odebrat.<br>
                <strong>K dispozici</strong> – uživatelé, kteří nejsou v týmu. Kliknutím na šipku je přidáš.
            </p>

            <h4>Role v týmu</h4>
            <ul style="padding-left:1.2rem;">
                <li><strong>Vedoucí</strong> – spravuje tým, může přiřazovat úkoly</li>
                <li><strong>Člen</strong> – běžný pracovník</li>
                <li><strong>Host</strong> – externí spolupracovník</li>
            </ul>

            <hr style="margin:20px 0; border:0; border-top:1px solid #e5e7eb;">

            <h4>Filozofie projektu</h4>
            <details>
                <summary style="cursor:pointer; font-weight:600;">Zobrazit</summary>
                <p style="margin-top:12px;">
                    Zakázka je Bůh. Aby se Bůh mohl realizovat, sestoupil a rozpadl se na úkoly.
                    Týmy jsou nástrojem, jak tyto úkoly naplnit – lidé v týmech spolupracují
                    a posouvají zakázku k dokončení.
                </p>
            </details>
        </div>
    </div>

</div> <!-- /.create-container -->
<?php require __DIR__ . '/../layout/footer.php'; ?>