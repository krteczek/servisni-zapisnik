<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
//use App\Core\Roles;
use App\Core\Csrf;


require __DIR__ . '/../layout/header.php';

/** @var array[] $tasks */

$workOrder = $view->order;
$team = $view->team;
$task = $view->task;
$post = $view->post;
$errors = $view->errors;


?>

<div class="create-container">
	<div class="card">

		<div class="card-section card-section--wo">
		    <div class="section-label">Úkol: </div>

		    <p>
		        <strong><?= e($workOrder['title']) ?></strong>
		        <span class="badge badge-status-<?= e($workOrder['status']) ?>">
		            <?= te($workOrder['status']) ?>
		        </span>
		    </p>

		    <div><?= tx($workOrder['description']) ?></div>
		</div>
		<!-- FORMULÁŘ -->
		<!-- Zobrazení chyb (stejné jako v report šabloně) -->
<?php require __DIR__ . '/../layout/formsErrors.php'; ?>		<div class="card-body">

			<!-- HLAVNÍ FORMULÁŘ -->
			<div class="form-container">
				<form method="post" 
						class="form" 
						autocomplete="off" 
						data-lpignore="true">
		
						<?= Csrf::getField() ?>
						<!-- jednotlivá pole formuláře TLAČÍTKA -->		
        <div class="form-group">
            <label>Název úkolu: <span class="req">*</span></label>
            <input type="text" name="title" value="<?= e($post['title'] ?? '') ?>"  required>
        </div>

        <div class="form-group">
            <label>Popis: </label>
            <textarea name="description" rows="10"><?= e($post['description'] ?? '') ?></textarea>
        </div>
<div class="form-group team-box">
    <label>Úkol je svěřen týmu: </label>

    <div class="team-box-card" style="--team-color: <?= e($team['color'] ?? '#ccc') ?>">
        <strong><?= e($team['name']) ?></strong>
        <p class="team-note">
            Úkol patří vždy jednomu týmu a nelze jej změnit.
            Můžete však:
        </p>


<a href="<?= Url::to('/{tenant}/tasks/' . (int) $task['id'] . '/clone/#main') ?>"
   class="btn btn-secondary"
   target="_blank"
   rel="noopener noreferrer"
   title="Otevře se v nové záložce">
    Klonovat tento úkol
    <span class="btn-icon">→</span>
</a>

</div>

        <div class="form-group">
<?php if ((int)($post['is_recurring'] ?? 0) !== 1): ?>
          <label>Z normálního úkolu nelze dodatečně udělat opakovaný úkol.</label>
<?php else: ?>      
          <label>
				Tento úkol je opakovací. Kliknutím na tlačítko <strong>Nastavit opakování úkolu</strong>
				se dostanete na stránku, kde můžete nastavit frekvenci opakování úkolu.
			 </label>
							<a href="<?= Url::to('/{tenant}/tasks/' . (int) $task['id'] . '/recurringEdit') ?>" 
								class="btn btn-secondary">
								<span class="btn-icon">←</span>
								Nastavit opakování úkolu
							</a>
              

<?php endif; ?>
        </div>

        </div>
		
						<!-- FORMULÁŘOVÉ TLAČÍTKA -->
						<div class="form-actions">
							<button type="submit" class="btn btn-primary">
								<span class="btn-icon"></span>
								Upravit úkol
							</button>
		
							<a href="<?= Url::to('/{tenant}/work-orders/' . (int) $workOrder['id'] . '/detail/#main') ?>" 
								class="btn btn-secondary">
								<span class="btn-icon">←</span>
								Na detail zakázky
							</a>

							<a href="<?= Url::to('/{tenant}/tasks/#main') ?>" 
								class="btn btn-secondary">
								<span class="btn-icon">←</span>
								Na výpis úkolů
							</a>
						</div>
		
				</form>
			</div>
		
		
			</div>
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
											
									</span>
								</p>
								</details>
								<h3>Nápověda:</h3>
								<p>
						<p>Náš systém je navržen tak, že úkoly mohou vznikat jen tehdy, patří-li nějaké zakázce. 
						Úkol bez zakázky, ke které by patřil, nám nedává smysl. </p>
						<p>Zde můžete již vytvořený úkol upravit. Protože zastávám názor,
						že zodpovědnost má být vždy jednoznačná, nelze změnit realizační tým po vytvoření úkolu.
						Lze ovšem vytvořit klon (nový úkol se stejným názvem i obsahem, který lze upravit) a
						nově vytvořenému úkolu přiřadit jiný realizační tým... 					
						</p>
						<p><strong>Důvod: </strong>Vždy je vidět, kdo má úkol na starosti a co v průběhu zodpovědnosti udělal.
						Snadno se to dohledává, snadno se to dokladuje. Neexistuje výmluva: tohle už měl dělat někdo jiný.</p>


		</div>
	</div>



</div>


<?php require __DIR__ . '/../layout/footer.php';


