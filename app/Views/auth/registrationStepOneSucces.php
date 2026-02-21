<?php
declare(strict_types=1);

use App\Core\Url;
use App\Core\Csrf;

require __DIR__ . '/../layout/header.php';

$old    = $view->data ?? [];
$errors = $view->errors ?? [];
?>
<h2>Dokončení registrace</h2>
<p>
Na Vámi uvedený email byly zaslány informace pro dokončení registrace.
Prosím, zkontrolujte si emailovou schránku a postupujte podle pokynů uvedených v našem emailu.
Děkujeme.
</p>


<?php require __DIR__ . '/../layout/footer.php'; ?>
