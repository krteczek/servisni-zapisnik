<?php require __DIR__ . '/../layout/header.php';
use App\Core\Url;
use App\Core\Csrf;
use App\Core\Access;

?>

<table border="1" cellpadding="8">
    <thead>
        <tr>
            <th>Email</th>
            <th>Číslo zaměstnance</th>
            <th>Jméno</th>
            <th>Role</th>
            <th>Stav</th>
            <th>Akce</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach (($view->users ?? []) as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['employee_number']) ?></td>
            <td>
                <?= htmlspecialchars(trim(
                    ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')
                )) ?>
            </td>
            <td><?= htmlspecialchars($user['global_role']) ?></td>
            <td>
                <?= $user['active'] ? 'Aktivní' : 'Neaktivní' ?>
            </td>

<td>
    <?php if (Access::can('users.edit')): ?>
        <a href="<?= Url::to('/users/' . (int)$user['id'] . '/edit') ?>">Upravit</a>
    <?php endif; ?>

    <?php if (Access::can('users.password')): ?>
        <a href="<?= Url::to('/users/' . (int)$user['id'] . '/password') ?>">Změnit heslo</a>
    <?php endif; ?>
</td>        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../layout/footer.php'; ?>
