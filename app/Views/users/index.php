<?php require __DIR__ . '/../layout/header.php'; 
use App\Core\Url;
use App\Core\Csrf;


?>

<table>
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
            	<a href="<?= Url::to('/users/' . (int)$user['id'] . '/edit') ?>">Upravit</a>
 
                <!-- připraveno do budoucna -->
                <a href="<?= Url::to('/users/' . (int)$user['id'] . '/password') ?>">Změnit heslo</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require __DIR__ . '/../layout/footer.php'; ?>
