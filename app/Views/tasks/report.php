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
$errors = $view->errors; // chyby validace

//var_dump($task, $teamMembers, $reports, $oldData);
//var_dump($teamMembers);
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
								<h3><?= e($task['title']) ?></h3>
				            <div class="card-body">
				                <?= tx($task['description'] ?? '') ?: 'Nespecifikováno'; ?>
				                
				            </div>
				        </div>
				    <?php endif; ?>
				</div>
				<!-- END zadaný úkol ke kterému jdeme přidávat reporty -->

				<!-- START: výpis chyb způsobených při vyplnování formuláře -->
            <?php require __DIR__ . '/../layout/formsErrors.php'; ?>
<?php if (in_array($task['status'], ['done', 'cancelled'])): ?>

    <div class="ui-alert ui-alert-info">
        Tento úkol je <?= ($task['status'] === 'done' ? 'uzavřený' : 'stornovaný') ?>, nelze k němu přidávat další reporty.
    </div>

<?php elseif ($task['canUserAddReport'] === true): ?>
				<!-- START: Formulář pro zadávání reportů k úkolům -->
				<div class="form-container">
					    <form method="POST" class="form">
					        <?= Csrf::getField() ?>
					        
					        <div class="form-group <?= isset($errors['report']) ? 'has-error' : '' ?>">
					            <label for="report">Report: (*)</label>
					            <textarea 
					                name="report" 
					                id="report" 
					                class="form-control" 
					                rows="5"
					            ><?= e($oldData['report'] ?? '') ?></textarea>
					            <?php if (isset($errors['report'])): ?>
					                <?php if (!empty($errors['report'])): ?>
    									<?php foreach ((array)$errors['report'] as $msg): ?>
        									<span class="error-message"><?= e($msg) ?></span><br>
    									<?php endforeach; ?>
									<?php endif; ?>
					            <?php endif; ?>
					        </div>
					        
					        <div class="form-group <?= isset($errors['kilometers']) ? 'has-error' : '' ?>">
					            <label for="kilometers">Kilometry:</label>
					            <input 
					                type="number" 
					                name="kilometers" 
					                id="kilometers" 
					                class="form-control" 
					                min="-9999" 
					                max="9999"
					                step="1"
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

            $checked = !empty($oldData['participants'][$userId]['selected']);
            $hours   = $oldData['participants'][$userId]['hours'] ?? '';
            $minutes = $oldData['participants'][$userId]['minutes'] ?? '';

            $userErrors = $errors['participants'][$userId] ?? [];
        ?>
            <div class="member-row <?= !empty($userErrors) ? 'has-error' : '' ?>">

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
                               min="-24"
                               max="24"
                               value="<?= e($hours) ?>">

                        <span class="time-separator">h</span>

                        <input type="number" 
                               name="participants[<?= $userId ?>][minutes]" 
                               class="time-input" 
                               placeholder="m" 
                               min="-59" 
                               max="59"
                               value="<?= e($minutes) ?>">

                        <span class="time-separator">m</span>
                    </div>
                </div>

                <!-- 🔥 chyby -->
                <?php if (!empty($userErrors)): ?>
                    <?php foreach ($userErrors as $msg): ?>
                        <span class="error-message"><?= e($msg) ?></span><br>
                    <?php endforeach; ?>
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
<?php else: ?>
<div class="ui-alert ui-alert-warning">
<p>Máte právo nahlížet do tohoto úkolu, ale nemáte právo přidávat k němu reporty.
Pokud chcete přidat report, musíte se nejprve stát členem týmu, který má tento úkol na starosti...</p>
</div>
<?php endif; ?>
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
					až budete vzpomínat, co jste tam dělali. Tady to najdete. A také: je to jediná povinná položka tohoto rozhraní.</li>
				<li><strong>Kilometry: </strong>Zapisujte ujeté kilometry. Abyste si mohli nakonci zakázky spočítat, kolik jste 
					celkem na této zakázce najezdili kilometrů.</li>
				<li><strong>Kdo pracoval: </strong>V týmu bývá více lidí, někdy jsou všichni v práci, jindy ne, někdy někdo 
					začne později nebo skončí dříve. Takový je život. Proto je zde možnost vybrat, kdo na tomto 
					úkole pracoval a jak dlouho.</li>
				<li>Systém funguje tak, že žádný odeslaný report nelze opravit ani odstranit. Můžete však napsat další report a
					v něm provést opravu. Čas i najeté kilometry je možno zadávat i v záporných hodnotách, takže když se někde spletete,
					můžete to v dalším reportu snadno opravit.</li>
				
			</ul>
		</div>
	</div>
	
</div>
<!-- Historie reportů -->
<div class="entity-grid" id="report_list">
    <?php foreach ($reports as $report): ?>
        <div class="report-item card">
            <div class="card-header">
                <strong class="card-title"><?= e($report['created_by_first_name'] . ' ' . $report['created_by_last_name']); ?></strong>
            	<small><?= e(date('d.m.Y H:i', strtotime($report['created_at']))); ?></small>
            </div>
            <div class="card-body">
            	<div class="meta-list">
            		<h4 class="card-header">Report: </h4>
            		<div class="meta-value">          
                		<?= tx($report['note']); ?>
                	</div>
                	<h4 class="card-header">Statistiky: </h4>
            		<div class="meta-item">
            			 
							<?php $hours = 0; ?>
							<h5 class="meta-label">Účastníci: </h5>
							<?php if (!empty($report['participants'])): ?>
                    		<?php foreach ($report['participants'] as $p): ?>
                    			<span class="meta-value">
                    				<?= e($p['first_name'] . ' ' . $p['last_name']); ?>:
										<?= formatMinutes($p['minutes_spent']); ?>
										<?php $hours += $p['minutes_spent']; ?>
                    			</span><br>
                    		<?php endforeach; ?>
							<?php else: ?>
								<span class="meta-value">Bez účasníků</span>
                   	<?php endif; ?>
                  </div>

                  <div class="meta-item">
                  	<span class="meta-label">Tým ujel: </span><span class="meta-value"><?= (int)($report['kilometers']); ?>km</span>
                  </div>
                  <div class="meta-item">
							<span class="meta-label">Tým odpracoval: </span><span class="meta-value"><?= formatMinutes($hours) ?></span>
						</div>
					</div>
            </div>
        </div>
    <?php endforeach; ?>
</div>


<?php require __DIR__ . '/../layout/footer.php'; ?>