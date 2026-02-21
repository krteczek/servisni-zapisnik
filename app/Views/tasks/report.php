<?php
declare(strict_types=1);
// app/views/tasks/report.php

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
<div class="create-container">
	<!-- START: Karta -->
	<div class="card">
			<!-- START: Tělo karty formuláře -->
			<div class="card-body">
				<!-- START: zadaný úkol ke kterému jdeme přidávat reporty -->
				<div class="task-detail">
				    <?php if (!empty($task['title'])): ?>
				        <div class="task-description card">
								<h3><?= e($task['title'] ?: 'Nespecifikováno') ?></h3>
				            <div class="card-body">
				                <?= nl2br(e($task['description'] ?: 'Nespecifikováno')); ?>
				            </div>
				        </div>
				    <?php endif; ?>
				</div>
				<!-- END zadaný úkol ke kterému jdeme přidávat reporty -->

				<!-- START: výpis chyb způsobených při vyplnování formuláře -->
				<?php if (!empty($errors)): ?>
				    <div class="ui-alert ui-alert-danger">
				        <ul>
								<?php foreach ($errors as $field => $error): ?>
									<?php if (is_array($error)): ?>
											<?php foreach ($error as $message): ?>
												<li><?= e($message) ?></li>
											<?php endforeach; ?>
									<?php else: ?>
											<li><?= e($error) ?></li>
									<?php endif; ?>
								<?php endforeach; ?>
				        </ul>
				    </div>
				<?php endif; ?>
				<!-- END: výpis chyb způsobených při vyplnování formuláře -->


				<!-- START: Formulář pro zadávání reportů k úkolům -->
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
					            <a href="<?= Url::to('/{tenant}/tasks/#main') ?>" class="btn btn-secondary">
					                Zpět na přehled
					            </a>
					        </div>
					    </form>
				</div>
				<!-- END: Formulář pro zadávání reportů k úkolům -->

			</div>
			<!-- END: Tělo karty formuláře -->

	</div>
	<div class="card card-help" id="helpCard">

		<div class="card-body">
			<h3>Filozofie projektu</h3>
			<details>
				<p>
					Když jsem přemýšlel nad vytvořením tohoto systému, měl jsem jasnou vizi: 
					<span>Zakázka je Bůh. Aby se Bůh mohl realizovat, zažít, naplnit, sestoupil k nám a rozpadl se 
							na jednotlivé úkoly.
							Skrze splnění těchto úkolů (reporty o vykonané práci), se Bůh, čili zakázka realizuje.
							Zde zapisujte, jak vám to šlo či nešlo, detaily a nebo prostě stručně. Je to pro Vás a Vešeho šéfa
							a hlavně pro vaše budoucí já, abyste si mohli připomenout, co se tady tehdy pos... dělalo.											
					</span>
				</p>
			</details>
			<h3>Nápověda:</h3>
			<p>
				Reporty slouží k evidenci udělané práce, času stráveného při práci a kilometrů ujetých při vykonání této práce.
			</p>
			
			<ul>
				<li><strong>Report: </strong>Jakákoli textová informace popisující vykonanou práci. Je to pro Vašeho šéfa. Je to i pro vás v budoucnu, 
					až budete vzpomínat, co jste tam dělali. Tady to najdete.</li>
				<li><strong>Kilometry: </strong>Zapisujte ujeté kilometry. Abyste si mohli nakonci zakázky spočítat, kolik jste 
					celkem na této zakázce najezdili kilometrů.</li>
				<li><strong>Kdo pracoval: </strong>V týmu bývá více lidí, někdy jsou všichni v práci, jindy ne, někdy někdo 
					začne později nebo skončí dříve. Takový je život. Proto je zde možnost vybrat, kdo na tomto 
					úkole pracoval a jak dlouho.</li>
				<li>Telefonní číslo není povinné.</li>
			</ul>
		</div>
	</div>
	
</div>
<!-- Zobrazení chyb -->

<!-- Formulář -->

</div>
<!-- Historie reportů -->
<div class="reports-grid" id="report_list">
    <?php foreach ($reports as $report): ?>
        <div class="report-item card">
            <div class="report-meta">
                <strong><?= e($report['created_by_first_name'] . ' ' . $report['created_by_last_name']); ?></strong>
                <small><?= e(date('d.m.Y H:i', strtotime($report['created_at']))); ?></small>
            </div>
            <div class="report-content">
                <?= nl2br(e($report['note'])); ?>
            </div>
            <?php $hours = 0; ?>
            <?php if (!empty($report['participants'])): ?>
                <div class="report-participants">
                    <strong>Pracovali:</strong>
                    <ul>
                        <?php foreach ($report['participants'] as $p): ?>
                            <li>
                                <?= e($p['first_name'] . ' ' . $p['last_name']); ?>: 
                                <?= formatMinutes($p['minutes_spent']); ?>
<?php $hours += $p['minutes_spent']; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <div class="">
            	Tým ujel: <?= (int)($report['kilometers']); ?>km

            	A celkem odpracoval: <?= formatMinutes($hours) ?>
            </div>
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
    padding: 15px;
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