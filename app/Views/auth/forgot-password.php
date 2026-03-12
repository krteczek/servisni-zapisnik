<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */


use App\Core\Csrf;
use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$err = $view->errors ?? [];
?>

<p>
Abychom Vám mohli zaslat informace pro resetování Vašeho přístupového hesla,
potřebujeme znát Váš email a jméno Vašeho pracovního prostoru.
</p>
<?php require __DIR__ . '/../layout/formsErrors.php'; ?>
<form method="post" action="">
    <?= Csrf::getField() ?>

    <div>
        <label>Email:</label>
        <input type="email" name="email" required>
        <p><?= e($err['email']) ?></p>
    </div>

    <div>
        <label>Pracovní prostor:</label>
        <input
            type="text"
            name="tenant"
            placeholder="např. servis-novak"
            title="Název, který jste zvolili při vytvoření pracovního prostoru."
            required
        >
        <p><?= e($err['tenant']) ?></p>
    </div>

    <button type="submit">
        Požádat o změnu hesla
    </button>
</form>

<p>
    <a href="<?= Url::to('/login') ?>" class="btn btn-secondary">
        ← Zpět na Přihlášení
    </a>
</p>

<?php require __DIR__ . '/../layout/footer.php'; ?>
