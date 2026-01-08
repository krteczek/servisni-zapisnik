<?php require __DIR__ . '/../layout/header.php'; ?>

<?php foreach ($view->data['tasks'] ?? [] as $task): ?>
    <div><?= htmlspecialchars($task['title']) ?></div>
<?php endforeach; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>
