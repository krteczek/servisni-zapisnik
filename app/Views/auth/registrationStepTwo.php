<?php
declare(strict_types=1);

use App\Core\Csrf;

require __DIR__ . '/../layout/header.php';

$old    = $view->data ?? [];
$errors = $view->errors ?? [];
//var_dump($errors);
?>

<div class="create-container">
    <div class="card">


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
        <label>Nové heslo <span class="req">*</span></label>
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
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>