<?php
/** @var App\Core\ViewContext $view */
use App\Core\Url;
use App\Core\Csrf;
use App\Core\Auth;
use App\Core\Roles;
?>
<!doctype html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <title>Bó: <?= htmlspecialchars($view->title ?: 'Servisní zápisník') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="<?= Url::to('/css/style.css') ?>">
</head>
<body>

<div class="app">

    <!-- ================= HEADER ================= -->
    <header class="header">
        <?php if (Auth::check()): ?>
            <div class="user-info">
                Přihlášen: <?= htmlspecialchars(Auth::label()) ?>
            </div>
        <?php endif; ?>

        <?php if (Auth::hasGlobalRole(['admin'])): ?>
            <small style="opacity:.6">
                Pohled jako: <strong><?= Auth::effectiveRole() ?></strong>
            </small>

            <div class="role-switch">
                <?php foreach (Roles::effective() as $key => $label): ?>
                    <a href="<?= Url::to('/admin/switch-role/' . $key) ?>">
                        [ <?= htmlspecialchars($label) ?> ]
                    </a>
                <?php endforeach; ?>
                | <a href="<?= Url::to('/admin/switch-role/reset') ?>">Admin</a>
            </div>
        <?php endif; ?>
    </header>

    <!-- ================= NAV ================= -->
    <nav class="nav">
        <ul class="menu">

            <?php foreach ($view->menu as $section): ?>
                <?php
                $hasSubmenu = !empty($section['items']);
                $classes = [
                    'menu-item',
                    $section['active'] ? 'active' : '',
                    $hasSubmenu ? 'has-submenu' : 'no-submenu',
                ];
                ?>

                <li class="<?= implode(' ', array_filter($classes)) ?>">

                    <?php if ($section['method'] === 'POST'): ?>
                        <form method="post"
                              action="<?= $section['path'] ?>"
                              onsubmit="return confirm('Opravdu se chcete odhlásit?');">
                            <?= Csrf::getField() ?>
                            <button type="submit">
                                <?= htmlspecialchars($section['label']) ?>
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="<?= $section['path'] ?>">
                            <?= htmlspecialchars($section['label']) ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($section['active'] && $hasSubmenu): ?>
                        <ul class="submenu">
                            <?php foreach ($section['items'] as $item): ?>
                                <li class="submenu-item <?= $item['active'] ? 'active' : '' ?>">
                                    <a href="<?= $item['path'] ?>">
                                        <?= htmlspecialchars($item['label']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                </li>
            <?php endforeach; ?>

        </ul>
    </nav>

    <!-- ================= MAIN ================= -->
    <main class="main">
        <h1><?= htmlspecialchars($view->title ?: 'Servisní zápisník') ?></h1>

        
    

