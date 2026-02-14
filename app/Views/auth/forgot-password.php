<?php
use App\Core\Csrf;
use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$errors = $view->errors ?? [];
?>
<?php if (!empty($view->error)): ?>
    <p style="color:#c62828; max-width:900px; margin:1rem auto;">
        <?= htmlspecialchars($view->errors) ?>
    </p>
<?php endif; ?>

<p>
Abychom Vám mohli zaslat informace pro resetování Vašeho přístupového hesla,
potřebujeme znát Váš email a jméno Vašeho pracovního prostoru.
</p>

<form method="post" action="">
    <?= Csrf::getField() ?>

    <div>
        <label>Email:</label>
        <input type="email" name="email" required>
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
