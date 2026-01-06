<?php
declare(strict_types=1);
use App\Core\Url;
?>
<ul class="submenu">
    <li><a href="<?= Url::to('/users') ?>">Přehled</a></li>

    <?php if ($view->user['global_role'] === 'admin'): ?>
        <li><a href="<?= Url::to('/users/create') ?>">Přidat uživatele</a></li>
    <?php endif; ?>
</ul>