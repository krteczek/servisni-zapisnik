<?php
declare(strict_types=1); 

/** @var \App\Core\ViewContext $view */
$errors = $view->errors;
//var_dump($errors);
?>
<?php if ($errors !== []): ?>
    <div class="ui-alert ui-alert-danger">
        <ul style="margin:0;">

            <?php
            $printErrors = function ($errs) use (&$printErrors) {
                foreach ($errs as $err) {
                    if (is_array($err)) {
                        $printErrors($err);
                    } else {
                        echo '<li>' . e($err) . '</li>';
                    }
                }
            };

            $printErrors($errors);
            ?>

        </ul>
    </div>
<?php endif; ?>