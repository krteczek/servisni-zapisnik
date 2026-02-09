<?php require __DIR__ . '/../layout/header.php'; ?>
<h1>Jejda… něco se pokazilo</h1>

<p>
Omlouváme se, došlo k technické chybě.
Vývojář byl informován a pravděpodobně už pije kafe s výrazem „aha…“.
</p>

<?php if (!empty($view->exception)): ?>
    <hr>
    <h3>Debug informace</h3>
    <pre><?= htmlspecialchars((string) $view->exception) ?></pre>
<?php endif; ?>
<?php require __DIR__ . '/../layout/footer.php'; ?>