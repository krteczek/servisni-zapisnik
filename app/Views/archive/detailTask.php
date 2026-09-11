<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
use App\Core\Csrf;
use App\Services\Tasks\TaskType;
use App\Services\Tasks\TaskStatus;
use App\Services\WorkOrders\WOStatus;


require __DIR__ . '/../layout/header.php';

$task = $view->task;
$reports = $view->reports;
$router = $view->router;
require __DIR__ . '/_filters.php';

//dd($task, $reports );
?>

<div class="create-container">
	<!-- START: Karta -->
	<div class="card">
			<!-- START: Tělo karty formuláře -->
			<div class="card-body">
				<!-- START: zadaný úkol -->
				<div class="task-detail">
				    <?php if ($task['title'] !== ''): ?>
				        <div class="task-description card" style="padding:5px">
							<h3 style="margin-bottom:0px;padding-bottom:0px;">Úkol: <?= e($task['title']) ?></h3>
				            <?php $description = tx($task['description'] ?? ''); ?>
							<div class="card-body">
								<?= $description !== '' ? $description : 'Nespecifikováno'; ?>
							</div>
				        </div>
				    <?php endif; ?>
				</div>
				<!-- END zadaný úkol -->
                 
                <?php
                $ahrefWO = '';
				
                if(WOStatus::isClosed($task['WOStatus'])) {
                    $ahrefWO = '<a href="' . Url::to('/{tenant}/archive/work-orders/' . (int) $task['work_order_id'] . '/detail/#main') . '" class="btn btn-secondary" title="Jít do Archivu na detail Zakázky k níž patří tento úkol">Archiv (detail Zakázky)</a>';

                } else {
					$url = Url::to('/{tenant}/work-orders/' . (int) $task['work_order_id'] . '/detail/#main'); 
					if($router->isAllowedRoute($url)) {
                    	$ahrefWO = '<a href="' . $url . '" class="btn btn-secondary" title="Jít na detail Zakázky k níž patří tento úkol">Detail Zakázky</a>';
                	}
				}
                ?>
                <p class="ui-alert ui-alert-warning">
                    Tento úkol je <?= (TaskStatus::isDone($task['status']) ? 'uzavřený' : 'stornovaný') ?>, nelze k němu přidávat další reporty.

                    </br></br>
					
		            	<a href="<?= Url::to('/{tenant}/tasks/#main') ?>" class="btn btn-secondary" title="Jít na výpis úkolů v archivu">Archiv (výpis úkolů)</a>
	                	<?= $ahrefWO; ?>
                    	</p>
					<?php if(TaskStatus::isDone($task['status'])) : ?>
                		<p>Práci na tomto úkolu vykonávalo celkem: <?= count($task['allReportsParticipants']) ?> pracovníků.</p>
                		<p>Bylo zapsáno celkem <?= count($reports) ?> reportů.</p>
                		<p>Práce vykonávali tito pracovníci: </p>
                
						<?php 
						$ahrefP = '';
						//dd($task['allReportsParticipants']);
						foreach ($task['allReportsParticipants'] as $participants) {
							$min = (int)$participants['minutes_spent'];
							$ahrefP .= '<a href="' . Url::to('/{tenant}/users/' . $participants['user_id'] . '/detail/#main') . '" title="jít na kartu pracovníka">' .  $participants['first_name'] . " " . $participants['last_name'] . ' (' . formatMinutes($min) . ')</a> ';

						}
						?>
						<p><?= $ahrefP;?></p>
						<p>Pracovníci najeli celkem <?= $task['totalKm'] ?> kilometrů</p>
						<p>Níže jsou vypsané všechny reporty tohoto úkolu.</p>
					<?php endif; ?>
			</div>
			<!-- END: Tělo karty formuláře -->

	</div>
	<div class="card card-help" id="helpCard">

		<div class="card-body">
			<details>
				<summary>Archiv</summary>
				<p>
					Archiv je místo, kam se přesouvají všechny dokončené nebo stornované úkoly a zakázky.
				</p>
				
				<ul>
                    <li><strong>Úkol: </strong>Zadání práce, kterou je potřeba vykonat.</li>
					<li><strong>Report: </strong>text popisující, co a jak pracovníci vykonali.</li>
					<li><strong>Kilometry: </strong>kolik tým během vykonávání prací na úkolu najezdil kilometrů.</li>
					<li><strong>Kdo pracoval: </strong>Seznam pracovníku včetně odkazů na jejich profily.</li>
                    <li><strong>Odpracovaný čas: </strong>Je zobrazen jak u jednotlivých pracovníků celkem, tak všech pracovníků dohromady i jednotlivých pracovníků u jednotlivých reportů. </li>
					
				</ul>
			</details>
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
							
							<?php if ($report['participants'] !== []): ?>
    						
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