<?php
declare(strict_types=1);


/** @var \App\Core\ViewContext $view */

require __DIR__ . '/../../layout/header.php';

require __DIR__ . '/_filters.php';
$tasks = $view->tasks ?? [];

?>


<form method="post">

<div class="flex gap-2 mb-2">
    <label>
        <input type="checkbox" id="toggleAll">
             Vybrat vše
    </label> 

</div>
<!-- zobrazení chyb z validace  end -->
<?php require __DIR__ . '/../../layout/formsErrors.php'; ?>
<!-- zobrazení chyb z validace  end -->
<div class="grid gap-2">

<?php if ($tasks === []): ?>
    <div class="p-2">Žádná data</div>
<?php endif; ?>

<?php foreach ($tasks as $task): ?>
    <label class="block border p-2 rounded">

        <div class="flex justify-between items-center">
            
            <div>
                <div>
                    <strong><?= e($task['title']) ?></strong>
                </div>

                <div class="text-sm opacity-70">
                    <?= e($task['team_name']) ?>
                    
                </div>
            </div>

            <div>
                <input type="checkbox" name="ids[]" value="<?= $task['id'] ?>">
            </div>

        </div>

    </label>
<?php endforeach; ?>

</div>

<div class="mt-3">
    <button type="submit">Export CSV</button>
</div>

</form>

