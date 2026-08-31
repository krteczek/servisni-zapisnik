<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;

require __DIR__ . '/../layout/header.php';
?>

<section class="page-hero">
    <h1>Servisní práce bez chaosu.</h1>

    <p>
        Bó – servisní zápisník pro zakázky, úkoly a servisní týmy.
    </p>

    <p>
        Mějte přehled o tom, co se má udělat,
        kdo na tom pracuje a co už je hotové.
    </p>

    <div class="page-actions">
        <a href="<?= Url::to('/login/') ?>" class="btn btn-primary">
            Přihlásit se
        </a>
    </div>
</section>

<section>
    <h2>Co Bó umí</h2>

    <div>
        <h3>Zakázky</h3>
        <p>
            Každá servisní práce má své místo.
            Od přijetí zakázky až po její dokončení.
        </p>
    </div>

    <div>
        <h3>Úkoly</h3>
        <p>
            Rozdělte zakázku na konkrétní práci
            a přiřaďte ji týmu.
        </p>
    </div>

    <div>
        <h3>Týmy</h3>
        <p>
            Každý ví, co má dělat a co čeká na vyřízení.
        </p>
    </div>

    <div>
        <h3>Evidence práce</h3>
        <p>
            Čas, kilometry a reporty máte přímo u práce,
            ke které patří.
        </p>
    </div>

    <div>
        <h3>Opakované úkoly</h3>
        <p>
            Pravidelná údržba se nemusí hlídat v hlavě.
            Bó ji vytvoří, když přijde její čas.
        </p>
    </div>
</section>

<section>
    <h2>Více práce. Méně papírování.</h2>

    <p>
        Bó vzniká pro lidi, kteří potřebují hlavně udělat práci.
        Proto je jednoduchý, rychlý a bez funkcí, které nikdo nepotřebuje.
    </p>
</section>

<section>
    <h2>Od zadání po hotovou práci.</h2>

    <p>
        Zakázka → úkol → tým → práce → report → hotovo.
    </p>
</section>

<section>
    <h2>Zkuste Bó.</h2>

    <p>
        Jednoduchý servisní zápisník pro váš tým.
    </p>

    <a href="<?= Url::to('/login/') ?>" class="btn btn-primary">
        Přihlásit se
    </a>
</section>

<?php require __DIR__ . '/../layout/footer.php'; ?>