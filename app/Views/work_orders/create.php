<?php require __DIR__ . '/../layout/header.php';
use App\Core\Url;
use App\Core\Csrf;
use App\Core\Access;
?>
<h1>Nová zakázka</h1>
<form method="post" action="<?= Url::to('/work-orders') ?>">
    <label>Externí číslo</label>
    <input name="external_number" value="<?= $this->e($this->data['external_number'] ?? '') ?>">

    <label>Název *</label>
    <input name="title" required value="<?= $this->e($this->data['title'] ?? '') ?>">

    <label>Popis</label>
    <textarea name="description"><?= $this->e($this->data['description'] ?? '') ?></textarea>

    <label>Zdroj</label>
    <select name="source">
        <option value="email">Email</option>
        <option value="phone">Telefon</option>
        <option value="personal">Osobně</option>
        <option value="system">Systém</option>
    </select>

    <label>Požadoval</label>
    <input name="requested_by" value="<?= $this->e($this->data['requested_by'] ?? '') ?>">

    <label>Priorita</label>
    <select name="priority">
        <option value="low">Nízká</option>
        <option value="normal">Normální</option>
        <option value="high">Vysoká</option>
        <option value="emergency">Havárie</option>
    </select>

    <button>Uložit</button>
</form>
<p>
    <a href="<?= Url::to('/work-orders') ?>" class="btn btn-secondary">
        ← Zpět na přehled
    </a>
</p>
<?php require __DIR__ . '/../layout/footer.php'; ?>