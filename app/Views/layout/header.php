<?php
/** @var App\Core\ViewContext $view */
use App\Core\Url;
use App\Core\Csrf;
use App\Core\Auth;
?>
<!doctype html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($view->title ?: 'Servisní zápisník') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSS -->
    <link rel="stylesheet" href="<?= Url::to('/css/style.css') ?>">
</head>
<body>

<header>

<ul class="menu">
<?php foreach ($view->menu as $section): ?>
    <li class="menu-item <?= $section['active'] ? 'active' : '' ?>">
        <?php if ($section['method'] === 'POST'): ?>
            <form method="post" action="<?= $section['path'] ?>">
                <?= Csrf::getField() ?>
                <button type="submit" class="menu-link">
                    <?= htmlspecialchars($section['label']) ?>
                </button>
            </form>
        <?php else: ?>
            <a href="<?= $section['path'] ?>">
                <?= htmlspecialchars($section['label']) ?>
            </a>
        <?php endif ?>
    </li>
<?php endforeach ?>
</ul>

<?php foreach ($view->menu as $section): ?>
<?php if ($section['active'] && $section['items']): ?>
<ul class="submenu">
<?php foreach ($section['items'] as $item): ?>
    <li class="submenu-item <?= $item['active'] ? 'active' : '' ?>">
        <a href="<?= $item['path'] ?>">
            <?= htmlspecialchars($item['label']) ?>
        </a>
    </li>
<?php endforeach ?>
</ul>
<?php endif ?>
<?php endforeach ?>

<?php if ($view->isLogged): ?>
<div class="user-info">
    Přihlášen: <?= htmlspecialchars(App\Core\Auth::label()) ?>
</div>
<?php endif ?>

</header>
