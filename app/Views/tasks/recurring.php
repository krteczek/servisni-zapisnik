<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
//use App\Core\Roles;
use App\Core\Csrf;
use App\Core\Config;
use App\Services\Tasks\TaskStatus;
use App\Services\Tasks\TaskType;

require __DIR__ . '/../layout/header.php';

/** @var array[] $tasks */

$workOrder = $view->order;
$teams = $view->teams;
$task = $view->task;
$errors = $view->errors;
$data = $view->data; 
//var_dump($post);
$frequencies = Config::get('recurring.frequencies');
$default = Config::get('recurring.default');
$limits = Config::get('recurring.limits');
$type = $data['frequency_type'] ?? null;

if (!$type || !array_key_exists($type, $frequencies)) {
    $data['frequency_type'] = $default['frequency_type'];
}
?>
<div class="create-container">
	<div class="card">
		<div class="card-section card-section--wo">
		    <div class="section-label">Úkol: </div>

		    <p>
		        <strong><?= e($task['title']) ?></strong>
		        <span class="badge badge-status-<?= e($task['status']) ?>">
		            <?= te($task['status']) ?>
		        </span>
		    </p>

		    <div><?= tx($task['description']) ?></div>
		</div>
		<!-- FORMULÁŘ -->
		<!-- Zobrazení chyb (stejné jako v report šabloně) -->
      <?php require __DIR__ . '/../layout/formsErrors.php'; ?>
      <div class="card-body">
         <div class="form-container">
			<form method="post">
				<?= Csrf::getField() ?>
               	<div class="form-group">
				    <label>Frekvence: </label>
					<select name="frequency_type">
						<?php foreach ($frequencies as $key => $value): ?>
							<option value="<?= e($key) ?>"
								<?= $key === ($data['frequency_type'] ?? $default['frequency_type']) ? 'selected' : '' ?>>
								<?= e($value) ?>
							</option>
						<?php endforeach; ?>
					</select>               
				</div>

                <div class="form-group">

				        <label>Interval: </label>
				        <input
				          type="number"
				          name="frequency_value"
				          value="<?= (int)($data['frequency_value'] ?? $default['frequency_value']) ?>"
				          min="<?= (int)$limits['min_frequency_value'] ?>"
				          max="<?= (int)$limits['max_frequency_value'] ?>"
				      >
                </div>
                <div class="form-group">

				      <label>Spustit v předstihu (dny): </label>
				      <input
				          type="number"
				          name="warning_days_before"
				          value="<?= (int)($data['warning_days_before'] ?? $default['warning_days_before']) ?>"
				          min="<?= (int)$limits['min_frequency_value'] ?>"
				          max="<?= (int)$limits['max_frequency_value'] ?>"
				          >
                </div>

                <div class="form-group">

				        <label>První spuštění: </label>
				        <input
				          type="date"
				          name="next_due_date"
				          value="<?= e($data['next_due_date'] ?? '') ?>"
				          >
                </div>

                <div class="form-group">

				      <label>
				        <input type="checkbox" name="active"
                        <?= ($data['active'] ?? 1) ? 'checked' : '' ?>
                        value="1"
                        >
				            Aktivní
				      </label>
                </div>
                <div class="form-actions">
					<button type="submit" class="btn btn-primary">
						<span class="btn-icon"></span>
						Uložit opakovaný úkol
					</button>

					<a href="<?= Url::to('/{tenant}/tasks/#main') ?>" 
						class="btn btn-secondary">
						<span class="btn-icon">←</span>
						Na výpis úkolů
					</a>
				</div>	
			</form>
			<div class="form-actions alert alert-warning">
				
				<?php if (((int)($data['active'] ?? 0) === 0) && TaskStatus::isOpen($task['status'])): ?>

					<?php 
						$action = $task['count_instances'] > 0 ? 'done' : 'canceled';
						$actionText = $task['count_instances'] > 0 ? 'Uzavřít' : 'Zrušit';
					
					?>

					<form method="post"
							action="<?= Url::to('/{tenant}/tasks/' . $task['id'] . '/recurring-' . $action . '/#main') ?>"
							data-confirm="Opravdu chcete úkol <?= $actionText ?>? Tato akce je nevratná..."
					>
					<?= Csrf::getField() ?>
						<button type="submit" class="btn btn-danger" title="<?= $actionText ?> úkol">
							<span class="btn-icon"></span>
							<?= $actionText ?> opakovací šablonu
						</button>
					</form>

				<?php endif; ?>
			</div>	

				
		  	</div>

	   	</div>
	</div>
	<div class="card card-help" id="helpCard">

		<div class="card-body">
			<details>
				<summary>Opakované úkoly</summary>
				<ul>
					<li>Frekvence: denně, týdně, měsíčně, ročně</li>
					<li>Interval: každých X dní, týdnů, měsíců, roků</li>
					<li>Spustit v předstihu: počet dní předem, kdy se má vygenerovat úkol pro upozornění</li>
					<li>První spuštění: datum, kdy se má poprvé vygenerovat úkol</li>
					<li>Aktivní: zaškrtnuto: úkoly jsou automaticky generované podle nastavení.</li>
					<li>Neaktivní: žádné nové úkoly se negenerují, ale stávající zůstávají.</li>
					<li>V neaktivním stavu lze teprve tento úkol ukončit, ale nelze ho znovu aktivovat.</li>
				</ul>

				<p>
    				<strong>Upozornění:</strong>
   					Aktivní master úkol nelze dokončit ani zrušit.
    				Nejprve je nutné deaktivovat opakování.
				</p>
				<p>Pomocí frekvence a intervalu můžete nastavit, jak často se má úkol opakovat. 
					Například "týdně" s intervalem "2" znamená, že se úkol bude generovat každé 2 týdny. 
					Nastavení "spustit v předstihu (3 dny)" znamená, že se úkol vygeneruje 3 dny před 
					jeho skutečným termínem, což umožní včasné upozornění.
					První spuštění určuje, kdy se má poprvé vygenerovat úkol, a aktivní/ neaktivní stav 
					umožňuje dočasně pozastavit generování úkolů, aniž byste museli mazat nastavení opakování.
				</p>
				<ul>
					<li>Opakující se úkoly jsou určeny pro pravidelné činnosti, které se musí 
						vykonávat opakovaně (např. údržba, kontroly, pravidelné schůzky).</li>
					<li>Opakující se úkoly se automaticky generují podle nastaveného vzorce 
						(denně, týdně, měsíčně) a mohou být spuštěny s předstihem pro včasné upozornění.</li>
					<li>Opakující se úkoly mají "master" záznam, který určuje jejich opakovací vzorec. 
						Generované úkoly jsou potomky tohoto master záznamu.</li>
					<li>Úprava master záznamu umožňuje změnit vzorec opakování pro všechny budoucí 
						generované úkoly.</li>
				</ul>
			</details>
		</div>
	</div>

	
</div>


<?php require __DIR__ . '/../layout/footer.php';

