<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */


use App\Core\Url;
use App\Core\Csrf;

require __DIR__ . '/../layout/header.php';

$old    = $view->data;
$errors = $view->errors;
?>


<div class="create-container">
    <div class="card">
    	<div class="card-body">
         <div class="ui-alert ui-alert-info">
            <strong>Registrace firmy:</strong>
            Po odeslání formuláře vám zašleme e-mail s odkazem pro dokončení registrace
            a nastavení hesla hlavního administrátora.
        </div>
        <?php require __DIR__ . '/../layout/formsErrors.php'; ?>
     		<div class="form-container">
   			<form method="post" 
						class="form user-form" 
						autocomplete="off" 
						data-lpignore="true">
		
						<?= Csrf::getField() ?>
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
									<span>Zakázka je Bůh. 
									Aby se Bůh mohl realizovat, zažít, naplnit, sestoupil k nám a rozpadl se na jednotlivé úkoly.
									Skrze splnění těchto úkolů (reporty o vykonané práci), se Bůh, čili zakázka realizuje. 
									</span>
								</p>
								<p>
									Abyste mohli využívat náš systém, je nutno do něj zaregistrovat Vaši firmu. 
								</p>
								
								</details>
								<h3>Nápověda:</h3>
								<p>
						Pro registraci uživatele je nutné zadat platnou emailovou adresu,
						na kterou bude odeslán aktivační email pro založení firemního účtu
						 a dalších základních informací, včetně nastavení hesla pro tento účet.
						</p>						

		</div>
	</div>



</div>

</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>