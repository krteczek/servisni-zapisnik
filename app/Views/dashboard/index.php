<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */


require __DIR__ . '/../layout/header.php'; ?>

<?php foreach ($view->data['tasks'] ?? [] as $task): ?>
    <div><?= e($task['title']) ?></div>
<?php endforeach; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
