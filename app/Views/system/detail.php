<?php
declare(strict_types=1);

use App\Core\Url;

/** @var \App\Core\ViewContext $view */


require __DIR__ . '/../layout/header.php';
$company = $view->company;
?>



<?= var_dump($company); ?>






<?php
require __DIR__ . '/../layout/footer.php';