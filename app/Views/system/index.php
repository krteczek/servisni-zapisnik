<?php
require __DIR__ . '/../layout/header.php';
$companies = $view->companies;
?>

<?php if (empty($companies)): ?>
    <p><em>Zatím nejsou založeny žádné firmy.</em></p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Název</th>
                <th>Slug</th>
                <th>Vytvořeno</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($companies as $company): ?>
                <tr>
                    <td><?= $company['id'] ?></td>
                    <td><?= htmlspecialchars($company['name']) ?></td>
                    <td><?= htmlspecialchars($company['slug']) ?></td>
                    <td><?= $company['created_at'] ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php endif ?>

<?php
require __DIR__ . '/../layout/footer.php';
?>
