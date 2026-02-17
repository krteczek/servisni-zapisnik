<?php
/** @var App\Core\ViewContext $view */
use App\Core\Url;
use App\Core\Csrf;
use App\Core\Auth;
use App\Core\Roles;
use App\Core\Session;

?>
<!doctype html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <title>Bó - <?= htmlspecialchars($view->title ?: 'Servisní zápisník') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="<?= Url::to('/favicon_io/favicon.ico') ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?= Url::to('/favicon_io/favicon-32x32.png') ?>">
<link rel="apple-touch-icon" href="<?= Url::to('/favicon_io/apple-touch-icon.png') ?>">
	 <link rel="stylesheet" href="<?= Url::to('/css/base.css') ?>">
    <link rel="stylesheet" href="<?= Url::to('/css/logo.css') ?>">
    <link rel="stylesheet" href="<?= Url::to('/css/menu.css') ?>">
    <link rel="stylesheet" href="<?= Url::to('/css/grid.css') ?>">
    <link rel="stylesheet" href="<?= Url::to('/css/card.css') ?>">
    <link rel="stylesheet" href="<?= Url::to('/css/badge.css') ?>">
    <link rel="stylesheet" href="<?= Url::to('/css/ui.css') ?>">
    <link rel="stylesheet" href="<?= Url::to('/css/buttons.css') ?>">
    <link rel="stylesheet" href="<?= Url::to('/css/teams.css') ?>">

    -->
 <style>
 <?php
//require __DIR__ . '/../../../public/css/base.css';
//require __DIR__ . '/../../../public/css/logo.css';
//require __DIR__ . '/../../../public/css/menu.css';
//require __DIR__ . '/../../../public/css/grid.css';
//require __DIR__ . '/../../../public/css/card.css';
//require __DIR__ . '/../../../public/css/badge.css';
//require __DIR__ . '/../../../public/css/ui.css';
//require __DIR__ . '/../../../public/css/buttons.css';
//require __DIR__ . '/../../../public/css/teams.css';
?>
 </style>
</head>
<body>

<div class="app">

    <!-- ================= HEADER ================= -->
    
<header class="header">
    <a href="<?= Url::to('/{tenant}/tasks') ?>"  class="logo">博</a>

    <?php if (Auth::check()): ?>
<div class="identity">
    <div class="identity-company">
        <?= htmlspecialchars(Auth::company()) ?>
    </div>

    <div class="identity-user">
        <?= htmlspecialchars(Auth::name() ?? 'Uživatel') ?>
    </div>

    <div class="identity-role">
        <?= htmlspecialchars(Auth::effectiveRole()) ?>
    </div>
</div>    <?php endif; ?>

    <?php if (Auth::hasGlobalRole(['admin'])): ?>
        <div class="role-switcher">
            <small class="role-switcher-label">
                Pohled jako: <strong><?= Auth::effectiveRole() ?></strong>
            </small>

            <div class="role-switch">
                <?php foreach (Roles::effective() as $key => $label): ?>
                    <a
                        href="<?= Url::to('/' . Auth::tenantSlug() . '/admin/switch-role/' . $key) ?>"
                        class="<?= Auth::effectiveRole() === $key ? 'active' : '' ?>"
                    >
                        <?= htmlspecialchars($label) ?>
                    </a>
                <?php endforeach; ?>
            </div>
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
                        <a href="<?= $section['path'] ?>/#main">
                            <?= htmlspecialchars($section['label']) ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($section['active'] && $hasSubmenu): ?>
                        <ul class="submenu">
                            <?php foreach ($section['items'] as $item): ?>
                                <li class="submenu-item <?= $item['active'] ? 'active' : '' ?>">
                                    <a href="<?= $item['path'] ?>/#main">
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
    <main id="main" class="main">
        <h1><?= htmlspecialchars($view->title ?: 'Servisní zápisník') ?></h1>

<?php if (!empty($_SESSION['flash'] ?? [])): ?>
    <div class="flash-wrapper">
        <?php foreach (['success','error','info'] as $type): ?>
            <?php if (Session::hasFlash($type)): ?>
                <div class="flash flash-<?= $type ?>">
                    <span class="flash-message">
                        <?= Session::getFlash($type) ?>
                    </span>
                    <button class="flash-close" onclick="this.parentElement.remove()">×</button>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

