

    <div class="task-form-context">
        Zakázka: <strong><?= e($workOrder['title']) ?></strong>
    </div>

    <h2>Nový úkol</h2>

<?php
$ch = '';
if ($err) {
    foreach ($err as $field => $messages) {
        foreach ($messages as $msg) {
            $ch .= '<p>' . $msg . '</p>
';
        }
    }
}	
?>


    <form method="post" action="<?= Url::to('/{tenant}/work-orders/' . $workOrder['id'] . '/tasks/create') ?>">
    <?= Csrf::getField() ?>
<?= e($ch) ?>
        <div class="form-group">
            <label>Název úkolu <span class="req">*</span></label>
            <input type="text" name="title" value="<?= e($post['title']) ?>"  required>
        </div>

        <div class="form-group">
            <label>Popis</label>
            <textarea name="description" required><?= e($post['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Tým <span class="req">*</span></label>
            <select name="team_id" required>
                <option value="">— vyber tým —</option>
            <?php foreach ($teams as $team): ?>
                <option
                    value="<?= $team['id'] ?>"
                    <?= (($post['team_id'] ?? null) == $team['id']) ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($team['name']) ?>
                </option>
            <?php endforeach; ?>
            </select>
        </div>


        <div class="form-actions">
            <button class="btn btn-success">Vytvořit úkol</button>
        </div>

    </form>
</div>
