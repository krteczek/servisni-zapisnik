<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
use App\Core\Csrf;

require __DIR__ . '/../layout/header.php';
?>


<p>
Tento dokument popisuje, jakým způsobem jsou zpracovávány osobní údaje
v aplikaci Bó – servisní zápisník.
</p>

<h2>Správce údajů</h2>

<p>
Správcem osobních údajů je Petr Vaněk, provozovatel aplikace Bó – servisní zápisník.
</p>

<h2>Jaké údaje zpracováváme</h2>

<ul>
    <li>identifikační údaje (např. název firmy, IČO),</li>
    <li>přihlašovací údaje (email, heslo – ukládáno v hashované podobě),</li>
    <li>provozní údaje (např. záznamy o aktivitě v aplikaci – audit log).</li>
</ul>

<h2>Za jakým účelem údaje zpracováváme</h2>

<p>
Osobní údaje jsou zpracovávány za účelem:
</p>

<ul>
    <li>zajištění funkčnosti aplikace,</li>
    <li>správy uživatelských účtů,</li>
    <li>evidence zakázek a činností,</li>
    <li>zabezpečení aplikace a prevence zneužití.</li>
</ul>

<h2>Právní základ zpracování</h2>

<p>
Zpracování osobních údajů je nezbytné pro poskytování služby a plnění smlouvy.
</p>

<h2>Doba uchování údajů</h2>

<p>
Osobní údaje jsou uchovávány po dobu trvání uživatelského účtu
a dále po nezbytně nutnou dobu pro zajištění právních povinností
nebo ochrany oprávněných zájmů správce.
</p>

<h2>Předávání údajů třetím stranám</h2>

<p>
Osobní údaje nejsou předávány třetím stranám,
s výjimkou případů stanovených zákonem.
</p>

<h2>Práva uživatele</h2>

<p>
Uživatel má právo:
</p>

<ul>
    <li>požádat o přístup ke svým údajům,</li>
    <li>požádat o opravu nepřesných údajů,</li>
    <li>požádat o výmaz údajů,</li>
    <li>vznést námitku proti zpracování.</li>
</ul>

<h2>Zabezpečení</h2>

<p>
Osobní údaje jsou chráněny přiměřenými technickými a organizačními opatřeními.
</p>

<p>Data mohou být technicky uložena na serverech poskytovatele hostingu.</p>

<?php require __DIR__ . '/../layout/footer.php'; ?>