<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */


use App\Core\Csrf;

require __DIR__ . '/../layout/header.php';

$old    = $view->data ?? [];
$errors = $view->errors ?? [];
//var_dump($errors);
?>

<div class="create-container">
    <div class="card">
        <div class="card-body">
            <?php require __DIR__ . '/../layout/formsErrors.php'; ?>
            <div class="form-container">

                <form method="post"
                      class="form"
                      autocomplete="off"
                      data-lpignore="true">

                    <?= Csrf::getField() ?>
                    
							<input type="hidden" name="token" value="<?= e($old['token'] ?? '') ?>">
                    <h3>Firma</h3>

                    <!-- Název firmy -->
                    <div class="form-group <?= isset($errors['name']) ? 'has-error' : '' ?>">
                        <label for="name">Název firmy <span class="req">*</span></label>
                        <input type="text"
                               name="name"
                               id="name"
                               class="form-control"
                               value="<?= e($old['name'] ?? '') ?>"
                               required>

							<?php if (isset($errors['name'])): ?>
							    <span class="error-message">
							        <?= e($errors['name'][0]) ?>
							    </span>
							<?php endif; ?>                        
                    </div>

                    <!-- IČO -->
                    <div class="form-group <?= isset($errors['ico']) ? 'has-error' : '' ?>">
                        <label for="ico">IČO <span class="req">*</span></label>
                        <input type="text"
                               name="ico"
                               id="ico"
                               class="form-control"
                               value="<?= e($old['ico'] ?? '') ?>"
                               placeholder="např. 12345678"
                               required>
									<?php if (isset($errors['ico'])): ?>
									    <span class="error-message">
									        <?= e($errors['ico'][0]) ?>
									    </span>
									<?php endif; ?>
                    </div>

                    <hr style="margin: 32px 0;">

                    <h3>Hlavní administrátor</h3>


                    <!-- Jméno a příjmení -->
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
									    <span class="error-message">
									        <?= e($errors['first_name'][0]) ?>
									    </span>
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
									    <span class="error-message">
									        <?= e($errors['last_name'][0]) ?>
									    </span>
									<?php endif; ?>
                        </div>
                        <div>
     <div class="form-group <?= isset($errors['password']) ? 'has-error' : '' ?>">
        <label>Nové heslo (min. 8 znaků)<span class="req">*</span></label>
        <input type="password" name="password" class="form-control" id="password" required>
    </div>
    <div class="form-group">
        <label>Nové heslo znovu <span class="req">*</span></label>
        <input type="password" name="passwordZ" class="form-control" required>
    </div>
                       
                        </div>
                    </div>

                    <div class="form-actions" style="margin-top: 32px;">
                        <button type="submit" class="btn btn-primary">
                            Založit firemní účet
                        </button>
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
			<ul>
				<li><strong>Název firmy: </strong>Obchodní jméno Vaší firmy</li>
				<li><strong>Ičo: </strong>Identifikační číslo Vaší firmy</li>
				<li><strong>Jméno a příjmení: </strong>Jméno člověka, který za Vaši firmu bude spravovat Váš prostor v Bó systému.</li>
				<li><strong>Heslo: </strong>Zvolte si své bezpečné heslo pro přihlášení do Bó systému.
				Minimální požadovaná délka hesla je 8 znaků.</li>
			</ul>
			<p>Po úspěšném vytvoření prostoru pro Vaši firmu budete automaticky přihlášení.</p>
			<p>V systému je vytvořena první zakázka a několik úkolů pro Vaše snažší seznámení s Bó systémem.</p>
	</div> 
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>