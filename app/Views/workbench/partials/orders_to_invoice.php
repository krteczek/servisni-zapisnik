<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */
$data = $view->WBData;
?>
<h2>Zakázky připravené k fakturaci [<?= count($data['ordersToInvoice']) ?>]</h2>
<p>Fakturace zakázek bude dostupná v další verzi aplikace.</p>
<p><small>Zakázka je kontejner pro úkoly. Fakturace zakázky bude souhrnem fakturovatelných úkolů v ní.</small></p>