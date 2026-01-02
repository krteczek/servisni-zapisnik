<?php
/** @var App\Core\ViewContext $view */
?>
<!doctype html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($view->title ?: 'Servisní zápisník') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<header>

        <?php foreach ($view->menu as $item): ?>
            <li>
                <?php if ($item['method'] === 'POST'): ?>
                    <form method="post" action="<?= htmlspecialchars('.' . $item['path']) ?>" style="display:inline">
                        <button type="submit">
                            <?= htmlspecialchars($item['label']) ?>
                        </button>
                    </form>
                <?php else: ?>
                    <a href="<?= htmlspecialchars('.' . $item['path']) ?>">
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>

    <?php if ($view->isLogged): ?>
        <div class="user-info">
            Přihlášen: <?= htmlspecialchars(\App\Core\Auth::label()) ?>
        </div>
    <?php endif; ?>
</header>

<main>
