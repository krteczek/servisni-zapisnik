<?php
declare(strict_types=1);

use App\Core\Url;
use App\Core\Roles;
use App\Core\Csrf;

$css = '';
//require __DIR__ . '/style.php';

require __DIR__ . '/../layout/header.php';

$old    = $view->data ?? [];
$errors = $view->errors ?? [];
$roles  = $view->roles ?? []; // předpokládám, že roles jsou v $view
?>

<div class="create-container">
	<div class="card">
		<!-- INFO ALERT (stejný styl jako v report šabloně) -->
		<div class="ui-alert ui-alert-info">
			<strong>Informace:</strong>
			Uživatel po vytvoření účtu obdrží aktivační e-mail,
			pomocí kterého si nastaví heslo a dokončí vytvoření účtu.
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
						class="form user-form" 
						autocomplete="off" 
						data-lpignore="true">
		
						<?= Csrf::getField() ?>
		
		
						<!-- FORMULÁŘOVÉ TLAČÍTKA -->
						<div class="form-actions">
							<button type="submit" class="btn btn-primary">
								<span class="btn-icon"></span>
								Vytvořit uživatele
							</button>
		
							<a href="<?= Url::to('/{tenant}/tasks') ?>/#main" 
								class="btn btn-secondary">
								<span class="btn-icon">←</span>
								Zpět na výpis úkolů
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
									<span>Zakázka je Bůh. Aby se Bůh mohl realizovat, zažít, naplnit, sestoupil k nám a rozpadl se na jednotlivé úkoly.
											Skrze splnění těchto úkolů (reporty o vykonané práci), se Bůh, čili zakázka realizuje. 
											Aby se úkoly mohly splnit, je potřeba (ještě stále) lidi. V tomhle rozhraní si je můžete přidat a nadále s 
											nimi v rámci našeho systému, komunikovat.
									</span>
								</p>
								</details>
								<h3>Nápověda:</h3>
								<p>
						Pro registraci uživatele je nutné zadat platnou emailovou adresu,
						na kterou bude odeslán aktivační email pro nastavení hesla.
						</p>
						
						<ul>
							<li>Email musí být unikátní v rámci organizace. Na jednu emailovou adresu nelze registrovat více pracovníků.</li>
							<li>Číslo zaměstnance slouží k interní identifikaci vrámci Vaší firmy (může se objevit v exportech práce).</li>
							<li>Jméno a příjmení slouží k identifikaci osoby (mohou se objevit v exportech).</li>
							<li>Telefonní číslo není povinné.</li>
						</ul>
						
						<h4>Role uživatelů</h4>
						
						<p>
						Role určují přístupová práva uživatele v rámci aplikace:
						</p>
						
						<ul>
							<li><strong>Admin</strong> – plný přístup ke všem funkcím systému. Zakládá uživatelské účty.</li>
							<li><strong>Mistr</strong> – zakládá a spravuje pracovní party, zakázky a úkoly.</li>
							<li><strong>Předák</strong> – koordinuje práci v rámci party a dohlíží na plnění úkolů.</li>
							<li><strong>Montér</strong> – vykonává práci a reportuje splněné úkoly.</li>
						</ul>

		</div>
	</div>



</div>


<?php require __DIR__ . '/../layout/footer.php'; ?>