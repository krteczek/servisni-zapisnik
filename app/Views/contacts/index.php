<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;

use App\Core\Csrf;


require __DIR__ . '/../layout/header.php';


$data = $view->data;
$errors = $view->errors; 
//var_dump($data);


?>

<div class="entity-grid">
<?php if($data === []): ?>
    <div class="users-empty">
        <strong>Žádní zákazníci</strong>
        <p>Zatím zde není žádný zákazník.</p>
    </div>

<?php else : ?>
<?php foreach ($data as $d): ?>
       <div class="card">
            <!-- HLAVIČKA KARTY -->
            <div class="card-header">
                <span class="card-title">
                    <a href="<?= Url::to('/{tenant}/contacts/' . (int)$d['id'] . '/detail/#main') ?>"
                       title="Jít na detail zákazníka">
                    <?= e($d['company_name']) ?>
                </a></span>                
            </div>

            <!-- TĚLO KARTY -->
            <div class="card-body">
                <div class="meta-list">
    						<div class="meta-item">
								<span class="meta-label">IČO: </span>
								<span class="meta-value"><?= e($d['ico'] ? $d['ico'] : '-') ?></span>
							</div>
   						<div class="meta-item">
								<span class="meta-label">DIČ: </span>
								<span class="meta-value"><?= e($d['dic'] ? $d['dic'] : '-') ?></span>
							</div>
   						<div class="meta-item">
								<span class="meta-label">Ulice: </span>
								<span class="meta-value"><?= e($d['street'] ? $d['street'] : '-') ?></span>
							</div>
   						<div class="meta-item">
								<span class="meta-label">Město: </span>
								<span class="meta-value"><?= e($d['city'] ? $d['city'] : '-') ?></span>
							</div>
   						<div class="meta-item">
								<span class="meta-label">PSČ: </span>
								<span class="meta-value"><?= e($d['zip'] ? $d['zip'] : '-') ?></span>
							</div>
   						<div class="meta-item">
								<span class="meta-label">Stát: </span>
								<span class="meta-value"><?= e($d['country'] ? $d['country'] : '-') ?></span>
							</div>
                   
                     <div class="meta-item">
                         <span class="meta-label">Email: </span>
                         <span class="meta-value"><?= e($d['email'] ? $d['email'] : '-') ?></span>
                     </div>
   						<div class="meta-item">
								<span class="meta-label">Telefon: </span>
								<span class="meta-value"><?= e($d['phone'] ? $d['phone'] : '-') ?></span>
							</div>
						</div>
					</div>

            <!-- PATIČKA KARTY -->
            <div class="card-footer">
                <div class="actions">

						  <a href="<?= Url::to('/{tenant}/contacts/' . (int)$d['id'] . '/edit/#main') ?>" 
                       class="btn btn-secondary" 
                       title="Upravit informace o zákazníkovi">
                        ✏️ Upravit
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php endif;?>
<?php require __DIR__ . '/../layout/footer.php';

