<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
use App\Core\Csrf;


$data = $view->data;
//var_dump($tasks);

$filters = $view->filters;

?>

<form method="get" class="filters">
    <div class="form-row">

        <!-- fulltext -->
        <input 
            type="text" 
            name="q" 
            placeholder="Hledat úkol nebo zakázku..."
            value="<?= e($filters['q'] ?? '') ?>"
        >

        <!-- status -->
        <select name="status">
            <option value="all">Vše</option>
            <option value="done" <?= ($filters['status'] ?? '') === 'done' ? 'selected' : '' ?>>
                Uzavřené
            </option>
            <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>
                Stornované
            </option>
        </select>

        <button type="submit" class="btn btn-primary">Filtrovat</button>

        <a href="<?= Url::current() ?>" class="btn btn-secondary">
            Zrušit filtry
        </a>

    </div>
</form>