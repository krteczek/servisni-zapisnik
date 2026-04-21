<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */


require __DIR__ . '/../layout/header.php';
$companies = $view->companies;
?>

<?php if (empty($companies)): ?>
    <p><em>Zatím nejsou založeny žádné firmy.</em></p>
<?php else: ?>
    <form method="get">
        <input type="text" name="search" value="<?= e($view->search ?? '') ?>" placeholder="Hledat firmu...">
        <button type="submit">Hledat</button>
    </form>
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
                <tr onclick="window.location='/system/companies/<?= (int)$company['id'] ?>'" style="cursor:pointer;">
                    <td><?= (int) $company['id'] ?></td>
                    <td><?= e($company['name']) ?></td>
                    <td><?= e($company['slug']) ?></td>
                    <td><?= date('d.m.Y H:i', strtotime($company['created_at'])) ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php endif ?>

<?php
require __DIR__ . '/../layout/footer.php';
?>
