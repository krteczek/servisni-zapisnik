<?php
declare(strict_types=1);
// app/views/tasks/report.php

/** @var \App\Core\ViewContext $view */

use App\Core\Csrf;
use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$task = $view->task;
$teamMembers = $view->teamMembers ?? []; // pole lidí z týmu
$reports = $view->reports ?? [];
$oldData = $view->oldData ?? []; // stará data z POST při chybě
$errors = $view->errors ?? []; // chyby validace

//var_dump($task);
//print_r($errors);
?>

<div class="task-detail">
    <?php if (!empty($task['description'])): ?>
        <div class="task-description card">
<h3><?= e($task['title']) ?></h3>
            <div class="card-body">
                <?= nl2br(e($task['description'])); ?>
            </div>
        </div>
    <?php endif; ?>

<!-- Zobrazení chyb -->
<?php if (!empty($errors)): ?>
    <div class="ui-alert ui-alert-danger">
        <ul>
            <?php foreach ($errors as $field => $error): ?>
                <li><?= e($error[0]) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Formulář -->
<div class="form-container">

    <form method="POST" class="form">
        <?= Csrf::getField() ?>
        
        <div class="form-group <?= isset($errors['report']) ? 'has-error' : '' ?>">
            <label for="report">Report:</label>
            <textarea 
                name="report" 
                id="report" 
                class="form-control" 
                rows="5"
            ><?= e($oldData['report'] ?? '') ?></textarea>
            <?php if (isset($errors['report'])): ?>
                <span class="error-message"><?= e($errors[0]['report']) ?></span>
            <?php endif; ?>
        </div>
        
        <div class="form-group <?= isset($errors['kilometers']) ? 'has-error' : '' ?>">
            <label for="kilometers">Kilometry:</label>
            <input 
                type="number" 
                name="kilometers" 
                id="kilometers" 
                class="form-control" 
                min="0" 
                max="9999" 
                value="<?= $oldData['kilometers'] ?? 0 ?>"
            >
            <?php if (isset($errors['kilometers'])): ?>
                <span class="error-message"><?= e($errors['kilometers']) ?></span>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label>Kdo pracoval?</label>
            <div class="member-list">
                <?php foreach ($teamMembers as $user): 
                    $userId = $user['id'];
                    $checked = isset($oldData['participants'][$userId]['selected']);
                    $hours = $oldData['participants'][$userId]['hours'] ?? '';
                    $minutes = $oldData['participants'][$userId]['minutes'] ?? '';
                ?>
                    <div class="member-row <?= isset($errors["participants[$userId]"]) ? 'has-error' : '' ?>">
                        <div class="member-checkbox">
                            <input 
                                type="checkbox" 
                                name="participants[<?= $userId ?>][selected]" 
                                id="user-<?= $userId ?>"
                                class="user-checkbox"
                                data-user-id="<?= $userId ?>"
                                <?= $checked ? 'checked' : '' ?>
                            >
                        </div>                        
                        <div class="member-name">
                            <label for="user-<?= $userId ?>">
                                <?= e($user['first_name'] . ' ' . $user['last_name']); ?>
                            </label>
                        </div>
                        
                        <div class="member-time" id="time-<?= $userId ?>" style="display: <?= $checked ? 'block' : 'none' ?>;">
                            <div class="time-input-group">
                                <input type="number" 
                                       name="participants[<?= $userId ?>][hours]" 
                                       class="time-input" 
                                       placeholder="h" 
                                       min="0"
                                       max="24"
                                       value="<?= e($hours) ?>">
                                <span class="time-separator">h</span>
                                <input type="number" 
                                       name="participants[<?= $userId ?>][minutes]" 
                                       class="time-input" 
                                       placeholder="m" 
                                       min="0" 
                                       max="59"
                                       value="<?= e($minutes) ?>">
                                <span class="time-separator">m</span>
                            </div>
                        </div>
                        <?php if (isset($errors["participants[$userId]"])): ?>
                            <span class="error-message"><?= e($errors["participants[$userId]"]) ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                Uložit report
            </button>
            <a href="/servisni-zapisnik/public/krteczek/tasks/#main" class="btn btn-secondary">
                Zpět na přehled
            </a>
        </div>
    </form>
</div>
</div>
<!-- Historie reportů -->
<div class="reports-grid" id="report_list">
    <?php foreach ($reports as $report): ?>
        <div class="report-item card">
            <!-- stejný obsah karty -->
                <strong><?= e($report['created_by_first_name'] . ' ' . $report['created_by_last_name']); ?></strong>
                <small><?= e(date('d.m.Y H:i', strtotime($report['created_at']))); ?></small>
            <div class="report-content">
                <?= nl2br(e($report['note'])); ?>
            </div>
            <?php if (!empty($report['participants'])): ?>
                <div class="report-participants">
                    <strong>Pracovali:</strong>
                    <ul>
                        <?php foreach ($report['participants'] as $p): ?>
                            <li>
                                <?= e($p['first_name'] . ' ' . $p['last_name']); ?>: 
                                <?= floor($p['minutes_spent'] / 60); ?>h <?= $p['minutes_spent'] % 60; ?>m
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
 
        </div>
    <?php endforeach; ?>
</div>
<div class="reports-list" id="report_list">
    <?php foreach ($reports as $report): ?>
        <div class="report-item card">
            <div class="report-meta">
                <strong><?= e($report['created_by_first_name'] . ' ' . $report['created_by_last_name']); ?></strong>
                <small><?= e(date('d.m.Y H:i', strtotime($report['created_at']))); ?></small>
            </div>
            <div class="report-content">
                <?= nl2br(e($report['note'])); ?>
            </div>
            <?php if (!empty($report['participants'])): ?>
                <div class="report-participants">
                    <strong>Pracovali:</strong>
                    <ul>
                        <?php foreach ($report['participants'] as $p): ?>
                            <li>
                                <?= e($p['first_name'] . ' ' . $p['last_name']); ?>: 
                                <?= floor($p['minutes_spent'] / 60); ?>h <?= $p['minutes_spent'] % 60; ?>m
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<!-- JavaScript pro zobrazení časových polí po zaškrtnutí -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    
    checkboxes.forEach(checkbox => {
        // Při načtení nastavíme správné zobrazení podle checked
        const userId = checkbox.dataset.userId;
        const timeDiv = document.getElementById('time-' + userId);
        if (checkbox.checked) {
            timeDiv.style.display = 'block';
        }
        
        checkbox.addEventListener('change', function() {
            const userId = this.dataset.userId;
            const timeDiv = document.getElementById('time-' + userId);
            
            if (this.checked) {
                timeDiv.style.display = 'block';
                // Nastavíme výchozí hodnoty jen pokud jsou prázdné
                const hoursInput = timeDiv.querySelector('.time-input:first-of-type');
                const minutesInput = timeDiv.querySelector('.time-input:last-of-type');
                if (hoursInput.value === '' && minutesInput.value === '') {
                    hoursInput.value = '1'; // default 1 hodina
                    minutesInput.value = '0';
                }
            } else {
                timeDiv.style.display = 'none';
            }
        });
    });
});
</script>

<!-- CSS pro chybové stavy -->
<style>
.has-error .form-control {
    border-color: #dc3545;
}
.has-error .error-message {
    color: #dc3545;
    font-size: 0.85rem;
    margin-top: 0.25rem;
    display: block;
}
.member-row.has-error {
    border-left: 3px solid #dc3545;
}


/* Omezíme šířku celého task-detail */
.task-detail {
    max-width: 800px;      /* stejné jako form-container */
    margin: 0 auto 2rem auto;  /* zarovnání na střed + odsazení dole */
}

/* Nebo pokud chceš, aby nadpis byl také v card */
.task-header-card {
    max-width: 800px;
    margin: 0 auto 1rem auto;
}

/* Grid pro reporty */
.reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

/* Aby karty v gridu měly stejnou výšku */
.reports-grid .report-item {
    display: flex;
    flex-direction: column;
    height: 100%;
    margin-bottom: 0;  /* zrušíme původní margin */
}

.reports-grid .report-content {
    flex: 1;  /* roztáhne se */
}

/* Pro mobil (menší než 768px) dáme jeden sloupec */
@media (max-width: 768px) {
    .reports-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require __DIR__ . '/../layout/footer.php'; ?>