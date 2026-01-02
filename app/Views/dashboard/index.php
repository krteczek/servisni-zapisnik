<?php require __DIR__ . '/../layout/header.php'; ?>

<h1><?= htmlspecialchars($view->title) ?></h1>

<?php foreach ($view->data['tasks'] ?? [] as $task): ?>
    <div><?= htmlspecialchars($task['title']) ?></div>
<?php endforeach; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
