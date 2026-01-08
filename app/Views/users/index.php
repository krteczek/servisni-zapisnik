<?php require __DIR__ . '/../layout/header.php'; ?>

<h1>Uživatelé</h1>
<table>
    <tr>
        <th>Email</th>
        <th>Jméno</th>
        <th>Akce</th>
    </tr>
<?php foreach (($view->users ?? []) as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td>
                <?= htmlspecialchars($user['first_name']) ?>
                <?= htmlspecialchars($user['last_name']) ?>
            </td>
            <td>
				    <a href="./users/<?= $user['id'] ?>">detail</a>
				    <a href="./users/<?= $user['id'] ?>/edit">upravit</a>
				</td>
        </tr>
    <?php endforeach; ?>
</table>
<?php require __DIR__ . '/../layout/footer.php'; ?>
