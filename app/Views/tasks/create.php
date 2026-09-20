<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
use App\Core\Csrf;


require __DIR__ . '/../layout/header.php';


$workOrder = $view->order;
$teams     = $view->teams;
$post      = $view->post;
$errors    = $view->errors; 



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
					
                    <?= (($post['team_id'] ?? null) === $team['id']) ? 'selected' : '' ?>
                >
                    <?= e($team['name']) ?>
                </option>
            <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
          <label class="checkbox" style="display:flex; align-items:center; gap:8px;">
              <input type="checkbox"
                     name="is_recurring_master"
                     value="1"
                     
					 <?= isset($post['is_recurring_master']) && $post['is_recurring_master'] === '1' ? 'checked' : '' ?>
					 
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
		
							<a href="<?= Url::to('/{tenant}/work-orders/' . $workOrder['id'] . '/detail/#main') ?>" 
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
			<details>
				<summary>Co je to úkol?</summary>
								
				
			        <p><strong>Informace:</strong>
			           Úkol je nějaká část zakázky, kterou má vykonat určitá osoba nebo tým. 
		           </p>
					<p>Náš systém je navržen tak, že úkoly mohou vznikat jen ze zakázky. 
						Úkol bez zakázky, ke které by patřil, nám nedává smysl. 
					</p>
					<p>Druhá a neméně důležitá podminka pro vznik úkolu je, že musí být přiřazen nějakému týmu. 
						Tím přenáší zodpovědnost za vykonání úkolů na konkrétní Tým a lidi v něm. 
						Oni Vám zpětně prostřednictvím reportů k úkolům, vykazují práci, čas a najeté kilometry.
					</p>
					<p>Úkoly jsou základním stavebním kamenem našeho systému, protože nám umožňují rozdělit 
						zakázku na menší části, které jsou snáze spravovatelné a sledovatelné. 
					</p>
					<p>
						Každému úkolu můžete nastavit termín dokončení. Systém potom barevně rozlišuje stavy: 
					</p>
					<ul>
						<li><span class="deadline-future">Zelená</span> - úkol je v pořádku, termín není blízko.</li>
						<li><span class="deadline-today">Oranžová</span> - úkol je blízko termínu dokončení (např. 3 dny předem).</li>
						<li><span class="deadline-late">Červená</span> - úkol je po termínu dokončení.</li>
						<li><span class="deadline-none">Černá</span> - úkol nemá nastavený termín dokončení.</li>
					</ul>
					
					<p>Zde úkol vytvoříte a přiřadíte ho některému z vašich aktivních týmů. 
						Tím přenášíte zodpovědnost za vykonání úkolů na konkrétní Tým a lidi v něm. 
						Oni Vám zpětně prostřednictvím reportů k úkolům, vykazují práci, čas a najeté kilometry.						
					</p>
			</details>

			<details>
				<summary>Opakující se úkoly</summary>

				<p>
					Opakující se úkoly slouží pro pravidelné činnosti,
					které se mají vykonávat opakovaně
					(například kontroly, údržba nebo pravidelné servisní návštěvy).
				</p>

				<p>
					Při vytvoření opakujícího se úkolu vznikne:
				</p>

				<ul>
					<li>
						<strong>Master úkol</strong> – šablona určující pravidla opakování.
					</li>

					<li>
						<strong>Generované úkoly</strong> – běžné pracovní úkoly,
						které vznikají automaticky podle nastaveného opakování.
					</li>
				</ul>

				<p>
					Reporty práce se zapisují pouze ke generovaným úkolům.
					Master úkol slouží pouze jako konfigurace opakování.
				</p>

				<p>
					Deaktivací opakování dojde k ukončení generování dalších úkolů.
				</p>
			</details>			
		</div>
	</div>



</div>


<?php require __DIR__ . '/../layout/footer.php';


