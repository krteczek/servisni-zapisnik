<?php require __DIR__ . '/../layout/header.php';
use App\Core\Url;
use App\Core\Csrf;
use App\Core\Access;
/** @var array $order */
$order = $this->view->order;
?>

<h1>Detail zakázky</h1>

<div class="card">

    <table class="table table-detail">
        <tr>
            <th>ID</th>
            <td><?= (int) $order['id'] ?></td>
        </tr>

        <tr>
            <th>Externí číslo</th>
            <td><?= htmlspecialchars($order['external_number'] ?? '—') ?></td>
        </tr>

        <tr>
            <th>Název</th>
            <td><strong><?= htmlspecialchars($order['title']) ?></strong></td>
        </tr>

        <tr>
            <th>Popis</th>
            <td><?= nl2br(htmlspecialchars($order['description'] ?? '')) ?></td>
        </tr>

        <tr>
            <th>Zdroj</th>
            <td><?= htmlspecialchars($order['source']) ?></td>
        </tr>

        <tr>
            <th>Požadoval</th>
            <td><?= htmlspecialchars($order['requested_by'] ?? '—') ?></td>
        </tr>

        <tr>
            <th>Kontakt</th>
            <td><?= htmlspecialchars($order['contact'] ?? '—') ?></td>
        </tr>

        <tr>
            <th>Priorita</th>
            <td>
                <span class="priority priority-<?= htmlspecialchars($order['priority']) ?>">
                    <?= htmlspecialchars($order['priority']) ?>
                </span>
            </td>
        </tr>

        <tr>
            <th>Stav</th>
            <td>
                <span class="status status-<?= htmlspecialchars($order['status']) ?>">
                    <?= htmlspecialchars($order['status']) ?>
                </span>
            </td>
        </tr>

        <tr>
            <th>Vytvořeno</th>
            <td><?= htmlspecialchars($order['created_at']) ?></td>
        </tr>

        <tr>
            <th>Uzavřeno</th>
            <td>
                <?= $order['closed_at']
                    ? htmlspecialchars($order['closed_at'])
                    : '<em>neuzavřeno</em>' ?>
            </td>
        </tr>
    </table>

</div>

<p>
    <a href="<?= Url::to('/work-orders') ?>" class="btn btn-secondary">
        ← Zpět na přehled
    </a>
</p>
