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
		
						<!-- EMAIL -->
						<div class="form-group <?= isset($errors['email']) ? 'has-error' : '' ?>">
							<label for="email">Email <span class="req">*</span></label>
							<input type="email"
									name="email"
									id="email"
									class="form-control"
									value="<?= e($old['email'] ?? '') ?>"
									placeholder="napr. jan.novak@firma.cz"
									required>
							<?php if (isset($errors['email'])): ?>
								<span class="error-message"><?= e($errors['email']) ?></span>
							<?php endif; ?>
						</div>
		
						<!-- ČÍSLO ZAMĚSTNANCE -->
						<div class="form-group <?= isset($errors['employee_number']) ? 'has-error' : '' ?>">
							<label for="employee_number">Číslo zaměstnance <span class="req">*</span></label>
							<input type="text"
									name="employee_number"
									id="employee_number"
									class="form-control"
									value="<?= e($old['employee_number'] ?? '') ?>"
									placeholder="napr. 12345"
									required>
							<?php if (isset($errors['employee_number'])): ?>
								<span class="error-message"><?= e($errors['employee_number']) ?></span>
							<?php endif; ?>
						</div>
		
					<div class="form-group <?= isset($errors['telefon']) ? 'has-error' : '' ?>">
							<label for="telefon">Telefonní číslo: </label>
							<input type="text"
									name="telefon"
									id="telefon"
									class="form-control"
									value="<?= e($old['telefon'] ?? '') ?>"
									placeholder="napr. +420 111 111 111"
									>
							<?php if (isset($errors['telefon'])): ?>
								<span class="error-message"><?= e($errors['telefon']) ?></span>
							<?php endif; ?>
						</div>
		
						<!-- JMÉNO A PŘÍJMENÍ VE DVOU SLOUPCÍCH -->
						<div class="form-row">
							<div class="form-group <?= isset($errors['first_name']) ? 'has-error' : '' ?>">
								<label for="first_name">Jméno <span class="req">*</span></label>
								<input type="text"
											name="first_name"
											id="first_name"
											class="form-control"
											value="<?= e($old['first_name'] ?? '') ?>"
											required>
								<?php if (isset($errors['first_name'])): ?>
										<span class="error-message"><?= e($errors['first_name']) ?></span>
								<?php endif; ?>
							</div>
		
							<div class="form-group <?= isset($errors['last_name']) ? 'has-error' : '' ?>">
								<label for="last_name">Příjmení <span class="req">*</span></label>
								<input type="text"
											name="last_name"
											id="last_name"
											class="form-control"
											value="<?= e($old['last_name'] ?? '') ?>"
											required>
								<?php if (isset($errors['last_name'])): ?>
										<span class="error-message"><?= e($errors['last_name']) ?></span>
								<?php endif; ?>
							</div>
						</div>
		
						<!-- ROLE -->
						<div class="form-group <?= isset($errors['global_role']) ? 'has-error' : '' ?>">
							<label for="global_role">Role <span class="req">*</span></label>
							<select name="global_role" id="global_role" class="form-control">
								<?php foreach ($roles as $key => $label): ?>
										<option value="<?= e($key) ?>"
											<?= ($key === ($old['global_role'] ?? Roles::default())) ? 'selected' : '' ?>>
											<?= e($label) ?>
										</option>
								<?php endforeach; ?>
							</select>
							<?php if (isset($errors['global_role'])): ?>
								<span class="error-message"><?= e($errors['global_role']) ?></span>
							<?php endif; ?>
						</div>
		
						<!-- FORMULÁŘOVÉ TLAČÍTKA -->
						<div class="form-actions">
							<button type="submit" class="btn btn-primary">
								<span class="btn-icon"></span>
								Vytvořit uživatele
							</button>
		
							<a href="<?= Url::to('/{tenant}/users') ?>/#main" 
								class="btn btn-secondary">
								<span class="btn-icon">←</span>
								Zpět na výpis uživatelů
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