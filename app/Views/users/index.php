<?php
declare(strict_types=1);

require __DIR__ . '/../layout/header.php';

use App\Core\Url;
use App\Core\Access;
?>

<table border="1" cellpadding="8">
    <thead>
        <tr>
            
            <th>Číslo zaměstnance</th>
            <th>Jméno</th>
            <th>Akce</th>
        </tr>
    </thead>
    <tbody>

    <?php foreach (($view->users ?? []) as $user): ?>
        <tr>
            

            <td><?= e($user['employee_number']) ?></td>

            <td>
                <?= e(trim(
                    ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')
                )) ?>
            </td>


            <td>
<?php 
//if (!\App\Core\UserGuard::isProtected($user)): 
?>
    
                <?php if (Access::can('users.edit')): ?>
                    <a href="<?= Url::to('/users/' . (int) $user['id'] . '/detail/#main') ?>">
                    Detail
                    </a>
                <?php endif; ?>

                <?php if (Access::can('users.edit')): ?>
                |
                    <a href="<?= Url::to('/users/' . (int) $user['id'] . '/edit/#main') ?>">
                        Upravit
                    </a>
                <?php endif; ?>

<?php 
//else: 
?>                
      <!--   <b>Účet nelze editovat</b>      -->
<?php 
//endif; 
?>
            </td>
        </tr>
    <?php endforeach; ?>

    </tbody>
</table>

<?php require __DIR__ . '/../layout/footer.php'; ?>