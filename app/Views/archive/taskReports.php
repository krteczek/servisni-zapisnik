<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
use App\Core\Csrf;
use App\Services\Tasks\TaskType;

require __DIR__ . '/../layout/header.php';

$data = $view->data;
$type = $view->type;
//var_dump($tasks);
require __DIR__ . '/_filters.php';
?>


<?php require __DIR__ . '/../layout/footer.php';