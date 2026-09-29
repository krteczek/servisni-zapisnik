<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;

use App\Core\Csrf;


require __DIR__ . '/../layout/header.php';


$data = $view->data;
$errors = $view->errors; 
//var_dump($data);
dc($data);

?>
