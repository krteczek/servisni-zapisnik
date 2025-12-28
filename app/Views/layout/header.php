<?php use App\Core\Menu; ?>

<nav>
    <ul>
        <?php foreach (Menu::items() as $item): ?>
            <li>
                <a href="<?= htmlspecialchars($item['url']) ?>">
                    <?= htmlspecialchars($item['label']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>