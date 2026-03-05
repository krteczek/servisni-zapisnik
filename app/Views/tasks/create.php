<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
//use App\Core\Roles;
use App\Core\Csrf;


require __DIR__ . '/../layout/header.php';

/** @var array[] $tasks */

$workOrder = $view->order;
$teams = $view->teams;
$post = $view->data;
$errors = $view->errors; 
//var_dump($workOrder);


?>

<div class="create-container">
	<div class="card">
		<div class="card-WO-header">
		<p>Nový úkol pro zakázku: <strong><?= e($workOrder['title']) ?></strong><span class="badge badge-status-<?= e($workOrder['status']) ?>"><?= te($workOrder['status']) ?></span></p>
		<div><?= e($workOrder['description']) ?></div>
		</div>
		<!-- INFO ALERT (stejný styl jako v report šabloně) -->
		<div class="ui-alert ui-alert-info">
			<strong>Informace:</strong>
			Úkol je nějaká část zakázky, kterou má vykonat určitá osoba nebo tým. 
		</div>
		<!-- FORMULÁŘ -->
		<!-- Zobrazení chyb (stejné jako v report šabloně) -->
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
            <textarea name="description"><?= e($post['description'] ?? '') ?></textarea>
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
						<p>Náš systém je navržen tak, že úkoly mohou vznikat jen tehdy, patří-li nějaké zakázce. 
						Úkol bez zakázky, ke které by patřil, nám nedává smysl. </p>
						<p>Zde úkol vytvoříte a přiřadíte ho některému z vašich aktivních týmů. 
						Tím přenášíte zodpovědnost za vykonání úkolů na konkrétní Tým a lidi v něm. 
						Oni Vám zpětně prostřednictvím reportů k úkolům, vykazují práci, čas a najeté kilometry.						
						</p>

		</div>
	</div>



</div>


<?php require __DIR__ . '/../layout/footer.php';


