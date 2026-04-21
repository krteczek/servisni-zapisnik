<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
use App\Core\Csrf;

/** @var array[] $teams */
$date = $view->data['date'] ?? null;
$teams = $view->teams;

?>

<form method="get" class="grid gap-2 mb-3">

    <div>
        <label>Datum</label>
        <input type="date" name="date" value="<?= e($date ?? '') ?>">
    </div>

    <div>
        <label>Tým</label>
        <select name="team_id">
            <option value="">Vše</option>
            <?php foreach ($teams as $team): ?>
                <option value="<?= (int) $team['id'] ?>">
                    <?= e($team['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label>Status</label>
        <select name="status">
            <option value="">Vše</option>
            <option value="open">Open</option>
            <option value="done">Done</option>
            <option value="cancelled">Cancelled</option>
        </select>
    </div>

    <button type="submit">Filtrovat</button>

</form>