<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

require __DIR__ . '/../layout/header.php';

use App\Core\Url;
use App\Core\Csrf;
use App\Core\Access;
$data = $view->data;
$teams = $view->teams; 
?>

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
                    <?php if(!isset($data['internal_number'])): ?>
                    Interní číslo zakázky bude vytvořeno až při uložení zakázky.
                    <?php else: ?>
                    <?= e($data['internal_number']) ?>
                    <?php endif; ?>
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
                        <option value="email" <?= ($data['source'] ?? '') === 'email' ? 'selected' : '' ?>><?= te('email') ?></option>
                        <option value="phone" <?= ($data['source'] ?? '') === 'phone' ? 'selected' : '' ?>><?= te('phone') ?></option>
                        <option value="personal" <?= ($data['source'] ?? '') === 'personal' ? 'selected' : '' ?>><?= te('personal') ?></option>
                        <option value="system" <?= ($data['source'] ?? '') === 'system' ? 'selected' : '' ?>><?= te('system') ?></option>
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
                <!-- Kontaktní osoba -->
                <div class="form-group">
                    <label>Kontaktní osoba</label>
                    <input
                        class="form-control"
                        name="contact_person"
                        value="<?= e($data['contact_person'] ?? '') ?>"
                    >
                </div>
                <!-- Priorita -->
                <div class="form-group">
                    <label>Priorita</label>
                    <select class="form-control" name="priority">
                        <option value="low" <?= ($data['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Nízká</option>
                        <option value="normal" <?= ($data['priority'] ?? '') === 'normal' ? 'selected' : '' ?>>Normální</option>
                        <option value="high" <?= ($data['priority'] ?? '') === 'high' ? 'selected' : '' ?>>Vysoká</option>
                        <option value="emergency" <?= ($data['priority'] ?? '') === 'emergency' ? 'selected' : '' ?>>Havárie</option>
                    </select>
                </div>

            <!-- Fakturace -->
                <div class="section-label">Zákazník: </div>

                <div class="form-group">
                    <label>Odběratel: </label>

                    <select name="contact_id" class="form-control">

                        <option value="">— bez zákazníka —</option>
                        <?php foreach ($view->contacts ?? [] as $c): ?>
                            <option value="<?= $c['id'] ?>"
                                <?= (($data['contact_id'] ?? null) == $c['id']) ? 'selected' : '' ?>
                            >
                                <?= e($c['company_name']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="form-group">
                    <label>Cena za hodinu (Kč)</label>
                    <input type="number" step="1" name="price_per_hour"
                        value="<?= e($data['price_per_hour'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Cena za km (Kč)</label>
                    <input type="number" step="1" name="price_per_km"
                        value="<?= e($data['price_per_km'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Automaticky vytvořit první úkol ze zakázky</label>
                <input 
                    type="checkbox" 
                    name="create_first_task"
                    id="create_task" 
                    value="1" <?= checked($data['create_first_task'] ?? false) ?>>
                    <small class="form-text text-muted">
                      Vytvoří se první úkol ihned po založení zakázky.
                    </small>
                </div>

                <div 
                    class="form-group" 
                    id="teamSelect" 
                    >
                    <label>Tým</label>
                    <select name="team_id" 
                            class="form-control"
                            <?= empty($data['create_first_task']) ? 'disabled' : '' ?>>
                            <?php foreach ($teams as $team): ?>
                            <option 
                                value="<?= $team['id'] ?>"
                                <?= (($data['team_id'] ?? null) == $team['id']) ? 'selected' : '' ?>
                                >
                                <?= e($team['name']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                   <small class="form-text text-muted">
                      Vyberte tým, který bude mít úkol na starosti.<br>
                      Každý úkol musí být přiřazen týmu – jasně daná zodpovědnost.
                    </small>
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


<script>
document.addEventListener('DOMContentLoaded', function () {
    
    const checkbox = document.getElementById('create_task');
    const teamSelect = document.getElementById('teamSelect');
    if (!checkbox || !teamSelect) return;

    function toggleTeam(focus = false) {
        const visible = checkbox.checked;

        teamSelect.style.display = visible ? 'block' : 'none';

        const select = teamSelect.querySelector('select');
        select.disabled = !visible;

        if (visible && focus) {
            select.focus();
        }
    }

    checkbox.addEventListener('change', function () {
        toggleTeam(true);
    });

    toggleTeam(); // init
});
</script>

<?php require __DIR__ . '/../layout/footer.php';

