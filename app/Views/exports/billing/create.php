<?php
declare(strict_types=1);

// views/teams/create.php – vytvoření týmu

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
use App\Core\Csrf;


require __DIR__ . '/../../layout/header.php';

$data   = $view->data;
$errors = $view->errors;
?>
<h1>Nový export</h1>

<form method="post">

    <label>Od:</label><br>
    <input type="date" name="period_from" required><br><br>

    <label>Do:</label><br>
    <input type="date" name="period_to" required><br><br>

    <label>Poznámka:</label><br>
    <textarea name="note"></textarea><br><br>

    <button type="submit">Vytvořit export</button>

</form>

<?php if (isset($_GET['error'])): ?>
    <p style="color:red;">
        Nepodařilo se vytvořit export.
    </p>
<?php endif; ?>