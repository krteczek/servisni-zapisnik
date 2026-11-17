<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$invoices = $view->invoices ?? [];
$filters  = $view->filters ?? [];

$search    = $filters['search'] ?? '';
$status    = $filters['status'] ?? 'all';
$order     = $filters['order'] ?? 'created_at';
$direction = $filters['direction'] ?? 'DESC';

$statusLabels = [
    'draft'     => 'Rozpracováno',
    'issued'    => 'Vystavená',
    'paid'      => 'Uhrazená',
    'cancelled' => 'Stornovaná',
];

$orderLabels = [
    'created_at'     => 'Datum vytvoření',
    'issued_at'      => 'Datum vystavení',
    'due_date'       => 'Datum splatnosti',
    'invoice_number' => 'Číslo faktury',
    'customer_name'  => 'Odběratel',
    'amount'         => 'Částka',
];
?>

<section id="main">

    <h1>Fakturace</h1>

    <?php if ($invoices === []) : ?>

        <p>Žádné faktury ani návrhy neodpovídají zadaným kritériím.</p>

    <?php else : ?>

        <p>Celkem záznamů: <?= count($invoices) ?></p>

    <?php endif; ?>

    <form method="get" action="<?= Url::to('/{tenant}/billing/invoice') ?>">

        <div>
            <label for="search">Vyhledávání</label>
            <input
                type="search"
                name="search"
                id="search"
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Číslo faktury nebo odběratel"
            >
        </div>

        <div>
            <label for="status">Stav</label>
            <select name="status" id="status">
                <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>
                    Všechny
                </option>

                <?php foreach ($statusLabels as $key => $label) : ?>
                    <option
                        value="<?= $key ?>"
                        <?= $status === $key ? 'selected' : '' ?>
                    >
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="order">Řadit podle</label>
            <select name="order" id="order">
                <?php foreach ($orderLabels as $key => $label) : ?>
                    <option
                        value="<?= $key ?>"
                        <?= $order === $key ? 'selected' : '' ?>
                    >
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="direction">Směr řazení</label>
            <select name="direction" id="direction">
                <option value="DESC" <?= $direction === 'DESC' ? 'selected' : '' ?>>
                    Sestupně
                </option>
                <option value="ASC" <?= $direction === 'ASC' ? 'selected' : '' ?>>
                    Vzestupně
                </option>
            </select>
        </div>

        <button type="submit">Použít</button>

    </form>

    <?php if ($invoices !== []) : ?>

        <table>
            <thead>
                <tr>
                    <th>Číslo faktury</th>
                    <th>Odběratel</th>
                    <th>Vystaveno</th>
                    <th>Splatnost</th>
                    <th>Částka</th>
                    <th>Stav</th>
                    <th>Akce</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($invoices as $invoice) : ?>

                    <tr>
                        <td>
                            <?= htmlspecialchars(
                                $invoice['status'] === 'draft'
                                    ? 'Návrh'
                                    : $invoice['invoice_number'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $invoice['customer_name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $invoice['status'] === 'draft'
                                    ? '—'
                                    : date('d. m. Y', strtotime($invoice['issued_at'])),
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>
                        </td>

                        <td>
                            <?php
                                if($invoice['status'] === 'draft') {
                                    echo '—';
                                } else {
                                    echo htmlspecialchars(
                                        date('d. m. Y', strtotime($invoice['due_date'])),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                }
                            ?>
                         </td>

                        <td>
                            <?= number_format(
                                (float) $invoice['total_amount'],
                                2,
                                ',',
                                ' '
                            ) ?> Kč
                        </td>

                        <td>
                            <?= $statusLabels[$invoice['status']] ?? 'Neznámý stav' ?>
                        </td>
                        <td>
                            <?php $invoiceDraft = $invoice['status'] === 'draft' ? 'draft/' : 'issued/'; ?>
                                <a href="<?= Url::to('/{tenant}/billing/invoice/' . $invoiceDraft . (int) $invoice['id'] . '/detail/#main') ?>"
                                    class="card-title"
                                    title="Otevřít detail faktury">
                                        <?= $invoice['status'] === 'draft'
                                            ? 'Návrh'
                                            : e($invoice['invoice_number']) ?>
                                    </a>
                        </td>
                    </tr>

                <?php endforeach; ?>

            </tbody>
        </table>

    <?php endif; ?>

</section>

<?php require __DIR__ . '/../layout/footer.php';

