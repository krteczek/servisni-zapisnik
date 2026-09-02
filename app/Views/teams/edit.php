<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
use App\Core\Csrf;

$team        = $view->team;
$rolesInTeam = $view->rolesInTeam;

require __DIR__ . '/../layout/header.php';

?>

<div class="create-container">

    <!-- LEVÝ SLOUPEC -->
    <div class="card">
        <div class="card-body">

            <!-- ÚVOD -->
            <div class="ui-alert ui-alert-info alert-spacing">
                <strong>Nastavení týmu</strong> – uprav název, barvu a spravuj členy.
            </div>
<?php require __DIR__ . '/../layout/formsErrors.php'; ?>
            <!-- ===================== -->
            <!-- ÚPRAVA TÝMU -->
            <!-- ===================== -->
            <form method="post" action="">
                <?= Csrf::getField() ?>

                <div class="form-group">
                    <label for="name">Název týmu <span class="req">*</span></label>
                    <input type="text"
                           id="name"
                           name="name"
                           class="form-control"
                           value="<?= e($team['name'] ?? '') ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="color">Barva týmu</label>

                    <div class="color-row">
                        <input type="color"
                               id="color"
                               name="color"
                               value="<?= e($team['color'] ?? '#2196F3') ?>"
                               class="color-input">

                        <span class="color-hint">
                            (barva pro označení v přehledech)
                        </span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        Uložit změny týmu
                    </button>
                </div>
            </form>

            <hr class="divider">

            <!-- ===================== -->
            <!-- ČLENOVÉ TÝMU -->
            <!-- ===================== -->

            <h2 class="section-title">Členové týmu</h2>

            <p class="section-subtext">
                Změň roli člena pomocí rozbalovací nabídky.
                Přidání nebo odebrání provedete kliknutím na šipky.
            </p>

            <div class="dual-list" id="changelist">

                <!-- V TÝMU -->
                <div>
                    <h3 class="subsection-title">V týmu</h3>

                    <?php if ($view->members === []): ?>
                        <div class="empty-box">
                            V tomhle týmu nikdo není
                        </div>
                    <?php endif; ?>

                    <?php foreach ($view->members as $m): ?>
                        <div class="member-row">

                            <form method="post"
                                  action="<?= Url::current() ?>#main"
                                  class="member-form">
                                <?= Csrf::getField() ?>
                                <input type="hidden"
                                       name="change_user_role"
                                       value="<?= (int) $m['membership_id'] ?>">

                                <select name="role_in_team"
                                    class="form-control js-auto-submit">
                                    <?php foreach ($rolesInTeam as $key => $label): ?>
                                        <option value="<?= e($key) ?>"
                                            <?= $key === $m['role_in_team'] ? 'selected' : '' ?>>
                                            <?= e($label) ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </form>

                            <span class="member-name">
                                <?= e($m['last_name'].' '.$m['first_name']) ?>
                            </span>

                            <form method="post"
                                  action="<?= Url::current() ?>#main"
                                  class="member-form">
                                <?= Csrf::getField() ?>
                                <input type="hidden"
                                       name="remove_membership_id"
                                       value="<?= (int) $m['membership_id'] ?>">

                                <button class="btn btn-secondary small-btn"
                                        title="Odebrat z týmu">
                                    <span class="arrow">→</span>
                                </button>
                            </form>

                        </div>
                    <?php endforeach ?>
                </div>

                <!-- K DISPOZICI -->
                <div>
                    <h3 class="subsection-title">K dispozici</h3>

                    <?php if ($view->availableUsers === []): ?>
                        <div class="empty-box">
                            Žádní další uživatelé
                        </div>
                    <?php endif; ?>

                    <?php foreach ($view->availableUsers as $u): ?>
                        <form method="post" class="member-row">
                            <?= Csrf::getField() ?>

                            <input type="hidden"
                                   name="add_user_id"
                                   value="<?= (int)$u['id'] ?>">

                            <input type="hidden"
                                   name="role_in_team"
                                   value="member">

                            <button class="btn btn-secondary small-btn"
                                    title="Přidat do týmu">
                                <span class="arrow">←</span>
                            </button>

                            <span class="member-name">
                                <?= e($u['last_name'].' '.$u['first_name']) ?>
                            </span>
                        </form>
                    <?php endforeach ?>
                </div>

            </div>

            <!-- ZPĚT -->
            <div class="section-footer">
                <a href="<?= Url::to('/{tenant}/teams') ?>/#main"
                   class="btn btn-secondary">
                    <span class="btn-icon">←</span>
                    Zpět na přehled týmů
                </a>
            </div>

        </div>
    </div>

    <!-- PRAVÝ SLOUPEC – NÁPOVĚDA -->
    <div class="card card-help" id="helpCard">
        <div class="card-body">

            <h3>Nápověda k týmům</h3>

            <h4>Úprava týmu</h4>
            <p>
                Můžeš změnit název a barvu týmu.
                Barva se zobrazuje v přehledech a kalendáři.
            </p>

            <h4>Správa členů</h4>
            <p>
                <strong>V týmu</strong> – zde jsou lidé, kteří už do týmu patří.
                Můžeš jim změnit roli nebo je odebrat.<br>
                <strong>K dispozici</strong> – uživatelé, kteří nejsou v týmu.
                Kliknutím na šipku je přidáš.
            </p>

            <h4>Role v týmu</h4>
            <ul class="help-list">
                <li><strong>Vedoucí</strong> – spravuje tým, může přiřazovat úkoly</li>
                <li><strong>Člen</strong> – běžný pracovník</li>
                <li><strong>Host</strong> – externí spolupracovník</li>
            </ul>

            <hr class="divider">

            <h4>Filozofie projektu</h4>

            <details>
                <summary class="help-summary">Zobrazit</summary>
                <p class="help-details-text">
                    Zakázka je Bůh. Aby se Bůh mohl realizovat, sestoupil a rozpadl se na úkoly.
                    Týmy jsou nástrojem, jak tyto úkoly naplnit – lidé v týmech spolupracují
                    a posouvají zakázku k dokončení.
                </p>
            </details>

        </div>
    </div>

</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
