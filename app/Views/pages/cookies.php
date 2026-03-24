<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
use App\Core\Csrf;

require __DIR__ . '/../layout/header.php';
?>



<p>
Tato aplikace používá pouze nezbytné (technické) cookies,
které jsou nutné pro její správnou funkci.
</p>

<h2>Jaké cookies používáme?</h2>

<ul>
    <li><strong>Session cookies</strong> – slouží k udržení přihlášení uživatele.</li>
    <li><strong>Bezpečnostní cookies</strong> – zajišťují ochranu aplikace (např. CSRF ochrana).</li>
</ul>

<h2>Proč cookies používáme?</h2>

<p>
Cookies jsou nezbytné pro:
</p>

<ul>
    <li>přihlášení do aplikace,</li>
    <li>udržení aktivní relace,</li>
    <li>zabezpečení aplikace proti zneužití.</li>
</ul>

<p>
Tyto cookies nevyžadují souhlas uživatele dle platné legislativy,
protože jsou nezbytné pro provoz služby.
</p>

<?php require __DIR__ . '/../layout/footer.php'; ?>