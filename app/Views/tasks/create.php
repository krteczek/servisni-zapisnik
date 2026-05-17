<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
//use App\Core\Roles;
use App\Core\Csrf;


require __DIR__ . '/../layout/header.php';

/** @var array[] $tasks */

$workOrder = $view->order;
$teams     = $view->teams;
$post      = $view->post;
$errors    = $view->errors; 
//var_dump($post);


?>

<div class="create-container">
	<div class="card">
		<div class="card-section card-section--wo">
		    <div class="section-label">Zakázka</div>

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
      <?php require __DIR__ . '/../layout/formsErrors.php'; ?>
	  		<div class="card-body">

			<!-- HLAVNÍ FORMULÁŘ -->
			<div class="form-container">
				<form method="post" 
						class="form" 
						autocomplete="off" 
						data-lpignore="true">
		
						<?= Csrf::getField() ?>
						<!-- jednotlivá pole formuláře TLAČÍTKA -->		
        <div class="form-group">
            <label>Název úkolu <span class="req">*</span></label>
            <input type="text" name="title" value="<?= e($post['title'] ?? '') ?>"  required>
        </div>

        <div class="form-group">
            <label>Popis</label>
            <textarea name="description" rows="10"><?= e($post['description'] ?? '') ?></textarea>
        </div>

		<div class="form-group">
			<label>Termín dokončení</label>
			<input type="date" name="due_date" value="<?= e($post['due_date'] ?? '') ?>">
		</div>

        <div class="form-group">
            <label>Tým <span class="req">*</span></label>
            <select name="team_id" required>
                <option value="">— vyber tým —</option>
            <?php foreach ($teams as $team): ?>
                <option
                    value="<?= $team['id'] ?>"
                    <?= (($post['team_id'] ?? null) == $team['id']) ? 'selected' : '' ?>
                >
                    <?= e($team['name'] ?? '') ?>
                </option>
            <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
          <label class="checkbox" style="display:flex; align-items:center; gap:8px;">
              <input type="checkbox"
                     name="is_recurring"
                     value="1"
                     <?= !empty($post['is_recurring']) ? 'checked' : '' ?>
                     style="width:auto;">
              <span>Opakující se úkol</span>
          </label>
     </div>
		
						<!-- FORMULÁŘOVÉ TLAČÍTKA -->
						<div class="form-actions">
							<button type="submit" class="btn btn-primary">
								<span class="btn-icon"></span>
								Vytvořit úkol
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
			        <p><strong>Informace:</strong>
			           Úkol je nějaká část zakázky, kterou má vykonat určitá osoba nebo tým. 
		           </p>
						<p>Náš systém je navržen tak, že úkoly mohou vznikat jen tehdy, patří-li nějaké zakázce. 
						Úkol bez zakázky, ke které by patřil, nám nedává smysl. </p>
						<p>Zde úkol vytvoříte a přiřadíte ho některému z vašich aktivních týmů. 
						Tím přenášíte zodpovědnost za vykonání úkolů na konkrétní Tým a lidi v něm. 
						Oni Vám zpětně prostřednictvím reportů k úkolům, vykazují práci, čas a najeté kilometry.						
						</p>

					<p><strong>Opakované úkoly</strong></p>
					<ul>
						<li>Opakující se úkoly jsou určeny pro pravidelné činnosti, které se musí vykonávat opakovaně (např. údržba, kontroly, pravidelné schůzky).</li>
						<li>Opakující se úkoly se automaticky generují podle nastaveného vzorce (denně, týdně, měsíčně) a mohou být spuštěny s předstihem pro včasné upozornění.</li>
						<li>Opakující se úkoly mají "master" záznam, který určuje jejich opakovací vzorec. Generované úkoly jsou potomky tohoto master záznamu.</li>
						<li>Úprava master záznamu umožňuje změnit vzorec opakování pro všechny budoucí generované úkoly.</li>
			</ul>

		</div>
	</div>



</div>


<?php require __DIR__ . '/../layout/footer.php';


