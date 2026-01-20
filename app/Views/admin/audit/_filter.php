<?php
declare(strict_types=1);

// views/teams/index.php – přehled týmů

use App\Core\Url;
$filters = $view->filters;
$logs = $view->logs;
?>

<form method="get">
    <input type="text" name="user_id" placeholder="User ID"
           value="<?= htmlspecialchars($filters['user_id'] ?? '') ?>">

    <select name="action">
        <option value="">-- akce --</option>
        <option value="create">create</option>
        <option value="update">update</option>
        <option value="delete">delete</option>
    </select>

    <button>Filtrovat</button>
    <a href="?">Zrušit filtry</a>
</form>