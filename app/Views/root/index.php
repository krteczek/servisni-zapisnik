<?php
declare(strict_types=1);

use App\Core\Url;

/** @var \App\Core\ViewContext $view */


require __DIR__ . '/../layout/header.php';
$companies = $view->companies;
//dd($companies);
?>

<?php if (is_array($companies) && $companies === []): ?>
    <p><em>Zatím nejsou založeny žádné firmy.</em></p>
<?php else: ?>
    <form method="get">
        <input type="text" name="search" value="<?= e($view->search) ?>" placeholder="Hledat firmu...">
        <button type="submit">Hledat</button>
    </form>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Slug</th>
                <th>Jméno firmy</th>
                <th>Název DataBáze</th>
                <th>IČO</th>
                <th>Aktivní?</th>
                <th>Aktivováno</th>
                <th>Onboarding start</th>
                <th>Onboarding finich</th>
                <th>Onboarding Status</th>
                <th>Onboarding error</th>
                <th>Vytvořeno</th>
            </tr>
        </thead>
        <tbody>

            <?php foreach ($companies as $company): ?>
                <tr onclick="window.location='/root/companies/<?= (int)$company['id'] ?>'" style="cursor:pointer;">
                    
                    <td><?= (int) $company['id'] ?></th>
                    <td><?= e($company['slug']) ?></td>
                    <td><a href="<?= Url::to('/{tenant}/root/detail-company/' . (int)$company['id']); ?>"><?= e($company['name']) ?></a></td>
                    <td><?= e($company['db_name']) ?></td>
                    <td><?= e($company['ico']) ?></td>
                    <td><?= e($company['active']) ?></td>
                    <td><?= e($company['activated_at']) ?></td>
                    <td><?= e($company['onboarding_started_at']) ?></td>
                    <td><?= e($company['onboarding_finished_at']) ?></td>
                    <td><?= e($company['onboarding_status']) ?></td>
                    <td><?= e($company['onboarding_error']) ?></td>
                    <td><?= e($company['created_at']) ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php endif ?>

<?php
require __DIR__ . '/../layout/footer.php';
?>
