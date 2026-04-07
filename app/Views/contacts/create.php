<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;

use App\Core\Csrf;


require __DIR__ . '/../layout/header.php';


$data = $view->data;
$errors = $view->errors; 
var_dump($data);


?>

<div class="create-container">
	<div class="card">
		<!-- FORMULÁŘ -->
		<!-- Zobrazení chyb (stejné jako v report šabloně) -->
      <?php require __DIR__ . '/../layout/formsErrors.php'; ?>
      <div class="card-body">

			<!-- HLAVNÍ FORMULÁŘ -->
			<div class="form-container">
				<form method="post">
				    <?= \App\Core\Csrf::getField() ?>

				    <div class="form-group">
				        <label>Jméno zákazníka/Název firmy: <span class="req">*</span></label>
				        <input type="text" name="company_name" value="<?= e($data['company_name'] ?? '') ?>" required>
				    </div>

				    <div class="form-group">
				        <label>IČO</label>
				        <input type="text" name="ico" value="<?= e($data['ico'] ?? '') ?>" >
				    </div>

				    <div class="form-group">
				        <label>DIČ</label>
				        <input type="text" name="dic" value="<?= e($data['dic'] ?? '') ?>" >
				    </div>
				    <div class="form-group">
				        <label>Ulice: <span class="req">*</span></label>
				        <input type="text" name="street" value="<?= e($data['street'] ?? '') ?>" required>
				    </div>
				    <div class="form-group">
				        <label>Město: <span class="req">*</span></label>
				        <input type="text" name="city" value="<?= e($data['city'] ?? '') ?>" required>
				    </div>
				    <div class="form-group">
				        <label>PSČ: <span class="req">*</span></label>
				        <input type="text" name="zip" value="<?= e($data['zip'] ?? '') ?>" required>
				    </div>
				    <div class="form-group">
				        <label>Stát: <span class="req">*</span></label>
				        <input type="text" name="country" value="<?= e($data['country'] ?? '') ?>" required>
				    </div>
				    <div class="form-group">
				        <label>Email</label>
				        <input type="text" name="email" value="<?= e($data['email'] ?? '') ?>" required>
				    </div>
				    <div class="form-group">
				        <label>Telefon</label>
				        <input type="text" name="phone" value="<?= e($data['phone'] ?? '') ?>" >
				    </div>
				    <div class="form-actions">

						<!-- FORMULÁŘOVÉ TLAČÍTKA -->
						
							<button type="submit" class="btn btn-primary">
								<span class="btn-icon"></span>
								Uložit zákazníka
							</button>
		
							<a href="<?= Url::to('/{tenant}/contacts/index/#main') ?>" 
								class="btn btn-secondary">
								<span class="btn-icon">←</span>
								Na výpis zákazníků
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
			        <p><strong>Zákazník:</strong>
			           Je ten, kdo po nás chce vykonat nějakou práci.
		           </p>
						<p>Je to tu zde, mimo jiné i proto, abyste mohli nakonec (jen neplátci DPH)
						vystavit fakturu za vámi odvedenou práci Vašim zákazníkům přímo z Bó systému.
						</p>
		
		</div>
	</div>



</div>


<?php require __DIR__ . '/../layout/footer.php';