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
					<?php
/** tady se vkládají formulářová políčka */
require __DIR__ . '/_contactForm.php';
					?>

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
			<details>
				<summary>Nápověda</summary>
				<p>Rozhraní <strong>Kontakty</strong> slouží k přidávání, editaci a správu kontaktů. 
					Kontakty můžete přidat zde, při zadávání zakázky do systému nebo při vytváření faktury.
				</p>
			</details>
		</div>
	</div>



</div>


<?php require __DIR__ . '/../layout/footer.php';