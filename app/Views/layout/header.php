<?php
/** @var App\Core\ViewContext $view */
use App\Core\Url;
use App\Core\Csrf;
use App\Core\Auth;
use App\Core\Roles;

$user = $view->user ?? null;
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
<?php if (Auth::hasGlobalRole(['admin'])): ?>
    <small style="opacity:.6">
        Pohled jako:
        <strong><?= Auth::effectiveRole() ?></strong>
    </small>

    <div style="background:#fee;padding:6px">
        Přepnout na pohled jako:
        <?php foreach (Roles::all() as $key => $label): ?>
            <a href="<?= Url::to('/admin/switch-role/' . $key) ?>">
                [ <?= htmlspecialchars($label) ?> ]
            </a>&nbsp;&nbsp;
        <?php endforeach; ?>

        | <a href="<?= Url::to('/admin/switch-role/reset') ?>">Admin</a>
    </div>
<?php endif; ?>
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

<?php if (!empty($view->flash)): ?>
    <div class="flash-messages">
        <?php foreach ($view->flash as $type => $messages): ?>
            <?php foreach ($messages as $message): ?>
                <div class="flash flash-<?= htmlspecialchars($type) ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (Auth::check()): ?>
<div class="user-info">
    Přihlášen: <?= htmlspecialchars(App\Core\Auth::label()) ?>
</div>
<?php endif ?>

</header>

<h1><?= htmlspecialchars($view->title ?: 'Servisní zápisník') ?></h1>