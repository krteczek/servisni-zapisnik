<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Csrf;
use App\Core\Url;

require __DIR__ . '/../layout/header.php';

$data = $view->data;

$invoice   = $data['invoice'] ?? [];
$supplier  = $data['supplier'] ?? [];
$customer  = $data['customer'] ?? [];
$workOrder = $data['workOrder'] ?? [];
$tasks     = $data['tasks'] ?? [];
$lines     = $data['lines'] ?? [];

$errors = $view->errors;

$contacts = $view->contacts ?? [];

$reportUrl = (string) ($data['report_url'] ?? '');

$units = $data['units'] ?? [
    'min' => 'min',
    'km'  => 'km',
    'ks'  => 'ks',
];

$priceUnits = $data['price_units'] ?? [
    'hod' => 'hod',
    'km'  => 'km',
    'ks'  => 'ks',
];

?>

<div class="create-container-invoice">

    <form method="post" id="invoice-form" data-next-line-index="<?= count($lines) ?>">

        <?= Csrf::getField() ?>

        <div class="invoice-layout">

            <?php require __DIR__ . '/../layout/formsErrors.php'; ?>

            <!-- =====================================================
                 LEVÁ ČÁST – 2/3
                 ===================================================== -->

            <main class="invoice-main">

                <h1>Vytvoření faktury</h1>

                <!-- =================================================
                     1. DODAVATEL
                     ================================================= -->

                <fieldset class="invoice-section">

                    <legend>Dodavatel</legend>

                    <div class="invoice-party">

                        <strong>
                            <?= e($supplier['official_name'] ?? '') ?>
                        </strong>

                        <?php if (!empty($supplier['ico'])): ?>
                            <div>
                                IČO:
                                <?= e($supplier['ico']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($supplier['dic'])): ?>
                            <div>
                                DIČ:
                                <?= e($supplier['dic']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($supplier['address'])): ?>
                            <div>
                                <?= e($supplier['address']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($supplier['city'])): ?>
                            <div>
                                <?= e($supplier['postal_code'] ?? '') ?>
                                <?= e($supplier['city']) ?>
                            </div>
                        <?php endif; ?>

                    </div>

                </fieldset>



<!-- =================================================
     2. ODBĚRATEL
     ================================================= -->

<fieldset class="invoice-section">

    <legend>Odběratel</legend>

    <div class="invoice-customer-form">

        <div class="form-group customer-select">

            <label for="contact_id">
                Existující zákazník
            </label>

            <select
                id="contact_id"
                name="contact_id"
            >
                <option value="">
                    — vyberte zákazníka —
                </option>

                <?php foreach ($contacts as $contact): ?>

                    <option
                        value="<?= (int) $contact['id'] ?>"
                        <?= (int) ($invoice['contact_id'] ?? 0)
                            === (int) $contact['id']
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e(
                            (string) (
                                $contact['official_name']
                                ?? ''
                            )
                        ) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <?php
        $data = $customer;
        require __DIR__ . '/../contacts/_contactForm.php';
        ?>

        <div class="form-group customer-save">

            <label>
                <input
                    type="checkbox"
                    name="save_customer"
                    value="1"
                    <?= !isset($invoice['save_customer'])
                        || $invoice['save_customer'] === true
                        ? 'checked'
                        : '' ?>
                >

                Uložit nového zákazníka /
                uložit případné změny
            </label>

        </div>

    </div>

</fieldset>


<!-- =================================================
     3. NASTAVENÍ FAKTURY
     ================================================= -->

<fieldset class="invoice-section">

    <legend>Nastavení faktury</legend>

    <div class="invoice-settings-grid">

        <div class="form-group invoice-title">

            <label for="title">
                Název faktury
            </label>

            <input
                type="text"
                id="title"
                name="title"
                value="<?= e(
                    (string) (
                        $invoice['title'] ?? ''
                    )
                ) ?>"
                required
            >

        </div>

        <div class="form-group invoice-date">

            <label for="issued_at">
                Datum vystavení
            </label>

            <input
                type="date"
                id="issued_at"
                name="issued_at"
                value="<?= e(
                    (string) (
                        $invoice['issued_at'] ?? ''
                    )
                ) ?>"
                required
            >

        </div>

        <div class="form-group invoice-date">

            <label for="due_date">
                Datum splatnosti
            </label>

            <input
                type="date"
                id="due_date"
                name="due_date"
                value="<?= e(
                    (string) (
                        $invoice['due_date'] ?? ''
                    )
                ) ?>"
                required
            >

        </div>

        <div class="form-group invoice-currency">

            <label for="currency">
                Měna
            </label>

            <select
                id="currency"
                name="currency"
            >
                <option
                    value="CZK"
                    <?= ($invoice['currency'] ?? 'CZK')
                        === 'CZK'
                        ? 'selected'
                        : '' ?>
                >
                    CZK
                </option>
            </select>

        </div>

    </div>

</fieldset>


<!-- =================================================
     4. NASTAVENÍ ZPRACOVÁNÍ
     ================================================= -->

<fieldset class="invoice-section">

    <legend>Nastavení zpracování</legend>

    <div class="form-group">

        <label for="processing_note">
            Poznámka ke zpracování
        </label>

        <textarea
            id="processing_note"
            name="processing_note"
            rows="3"
        ><?= e(
            (string) (
                $invoice['processing_note'] ?? ''
            )
        ) ?></textarea>

    </div>

</fieldset>


                <!-- =================================================
                     5. PRÁCE A PENÍZE
                     ================================================= -->

                <fieldset class="invoice-section">

                    <legend>Práce a peníze</legend>

                    <?php foreach ($tasks as $taskIndex => $task): ?>

                        <?php
                        $taskId = (int) (
                            $task['task_id'] ?? 0
                        );

                        $taskMinutes = (int) (
                            $task['minutes'] ?? 0
                        );

                        $taskHours = intdiv(
                            $taskMinutes,
                            60
                        );

                        $taskRemainingMinutes =
                            $taskMinutes % 60;

                        $taskKilometers = (float) (
                            $task['kilometers'] ?? 0
                        );
                        ?>

                        <div
                            class="invoice-task"
                            data-task-id="<?= $taskId ?>"
                        >

                            <div class="invoice-task-header">

                                <h3>
                                    <?= e(
                                        (string) (
                                            $task['title'] ?? ''
                                        )
                                    ) ?>
                                </h3>

                                <input
                                    type="hidden"
                                    name="tasks[<?= $taskIndex ?>][task_id]"
                                    value="<?= $taskId ?>"
                                >

                                <input
                                    type="hidden"
                                    name="tasks[<?= $taskIndex ?>][title]"
                                    value="<?= e(
                                        (string) (
                                            $task['title'] ?? ''
                                        )
                                    ) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="tasks[<?= $taskIndex ?>][minutes]"
                                    value="<?= $taskMinutes ?>"
                                >

                                <input
                                    type="hidden"
                                    name="tasks[<?= $taskIndex ?>][kilometers]"
                                    value="<?= $taskKilometers ?>"
                                >

                            </div>


                            <div class="invoice-task-summary">

                                <div>
                                    <strong>Skutečný čas:</strong>

                                    <?= $taskHours ?> h

                                    <?php if ($taskRemainingMinutes > 0): ?>
                                        <?= $taskRemainingMinutes ?> min
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <strong>Skutečné km:</strong>

                                    <?= e(
                                        (string) $taskKilometers
                                    ) ?>
                                    km
                                </div>

                            </div>


                            <div class="invoice-task-lines">

                                <div class="invoice-lines-header">

                                    <strong>
                                        Fakturační položky
                                    </strong>

                                </div>

                                <?php
                                $taskLines = [];

                                foreach ($lines as $lineIndex => $line) {
                                    if (
                                        (int) (
                                            $line['task_id'] ?? 0
                                        ) === $taskId
                                    ) {
                                        $taskLines[$lineIndex] = $line;
                                    }
                                }
                                ?>

                                <div
                                    class="invoice-lines"
                                    data-task-lines="<?= $taskId ?>"
                                >

                                    <?php foreach (
                                        $taskLines
                                        as $lineIndex => $line
                                    ): ?>

                                        <?php
                                        $lineUnit = (string) (
                                            $line['unit'] ?? ''
                                        );

                                        $lineQuantity =
                                            $line['quantity'] ?? 0;

                                        $linePriceUnit =
                                            (string) (
                                                $line['price_unit']
                                                ?? ''
                                            );

                                        $lineUnitPrice =
                                            $line['unit_price'] ?? 0;

                                        $lineCurrency =
                                            (string) (
                                                $line['currency']
                                                ?? 'CZK'
                                            );
                                        ?>

                                        <div
                                            class="invoice-line"
                                            data-line-index="<?= (int) $lineIndex ?>"
                                        >

                                            <input
                                                type="hidden"
                                                name="lines[<?= (int) $lineIndex ?>][task_id]"
                                                value="<?= $taskId ?>"
                                            >

                                            <div class="form-group">

                                                <label>
                                                    Popis
                                                </label>

                                                <textarea
                                                    name="lines[<?= (int) $lineIndex ?>][description]"
                                                    rows="2"
                                                ><?= e(
                                                    (string) (
                                                        $line['description']
                                                        ?? ''
                                                    )
                                                ) ?></textarea>

                                            </div>

                                            <div class="invoice-line-fields">

                                                <div class="form-group">

                                                    <label>
                                                        Množství
                                                    </label>

                                                    <input
                                                        type="number"
                                                        step="0.001"
                                                        min="0"
                                                        name="lines[<?= (int) $lineIndex ?>][quantity]"
                                                        value="<?= e(
                                                            (string) $lineQuantity
                                                        ) ?>"
                                                    >

                                                </div>

                                                <div class="form-group">

                                                    <label>
                                                        Jednotka
                                                    </label>

                                                    <select
                                                        name="lines[<?= (int) $lineIndex ?>][unit]"
                                                    >

                                                        <?php foreach (
                                                            $units
                                                            as $unitValue => $unitLabel
                                                        ): ?>

                                                            <option
                                                                value="<?= e(
                                                                    (string) $unitValue
                                                                ) ?>"
                                                                <?= $lineUnit === (string) $unitValue
                                                                    ? 'selected'
                                                                    : '' ?>
                                                            >
                                                                <?= e(
                                                                    (string) $unitLabel
                                                                ) ?>
                                                            </option>

                                                        <?php endforeach; ?>

                                                    </select>

                                                </div>

                                                <div class="form-group">

                                                    <label>
                                                        Cena za
                                                    </label>

                                                    <select
                                                        name="lines[<?= (int) $lineIndex ?>][price_unit]"
                                                    >

                                                        <?php foreach (
                                                            $priceUnits
                                                            as $priceUnitValue => $priceUnitLabel
                                                        ): ?>

                                                            <option
                                                                value="<?= e(
                                                                    (string) $priceUnitValue
                                                                ) ?>"
                                                                <?= $linePriceUnit
                                                                    === (string) $priceUnitValue
                                                                    ? 'selected'
                                                                    : '' ?>
                                                            >
                                                                <?= e(
                                                                    (string) $priceUnitLabel
                                                                ) ?>
                                                            </option>

                                                        <?php endforeach; ?>

                                                    </select>

                                                </div>

                                                <div class="form-group">

                                                    <label>
                                                        Cena
                                                    </label>

                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        name="lines[<?= (int) $lineIndex ?>][unit_price]"
                                                        value="<?= e(
                                                            (string) $lineUnitPrice
                                                        ) ?>"
                                                    >

                                                </div>

                                                <div class="form-group">

                                                    <label>
                                                        Měna
                                                    </label>

                                                    <select
                                                        name="lines[<?= (int) $lineIndex ?>][currency]"
                                                    >
                                                        <option
                                                            value="CZK"
                                                            <?= $lineCurrency === 'CZK'
                                                                ? 'selected'
                                                                : '' ?>
                                                        >
                                                            CZK
                                                        </option>
                                                    </select>

                                                </div>

                                                <div class="form-group">

                                                    <label>
                                                        Celkem
                                                    </label>

                                                    <output
                                                        class="invoice-line-total"
                                                    >
                                                        <?= e(
                                                            number_format(
                                                                (float) (
                                                                    $line['total']
                                                                    ?? 0
                                                                ),
                                                                2,
                                                                ',',
                                                                ' '
                                                            )
                                                        ) ?>
                                                    </output>

                                                </div>

                                                <div class="form-group invoice-line-remove">

                                                    <button
                                                        type="button"
                                                        class="btn btn-secondary js-remove-line"
                                                    >
                                                        Odebrat
                                                    </button>

                                                </div>

                                            </div>

                                        </div>

                                    <?php endforeach; ?>

                                </div>


                                <button
                                    type="button"
                                    class="btn btn-secondary js-add-task-line"
                                    data-task-id="<?= $taskId ?>"
                                >
                                    + Přidat fakturační položku k tomuto úkolu
                                </button>

                            </div>

                        </div>

                    <?php endforeach; ?>


                    <!-- =================================================
                         OSTATNÍ FAKTURAČNÍ POLOŽKY
                         ================================================= -->

                    <div class="invoice-other-lines">

                        <h3>
                            Ostatní fakturační položky
                        </h3>

                        <div
                            id="other-invoice-lines"
                            class="invoice-lines"
                        >

                            <?php foreach (
                                $lines
                                as $lineIndex => $line
                            ): ?>

                                <?php
                                $lineTaskId = (int) (
                                    $line['task_id'] ?? 0
                                );

                                if ($lineTaskId !== 0) {
                                    continue;
                                }
                                ?>

                                <div
                                    class="invoice-line"
                                    data-line-index="<?= (int) $lineIndex ?>"
                                >

                                    <input
                                        type="hidden"
                                        name="lines[<?= (int) $lineIndex ?>][task_id]"
                                        value=""
                                    >

                                    <div class="form-group">

                                        <label>
                                            Popis
                                        </label>

                                        <textarea
                                            name="lines[<?= (int) $lineIndex ?>][description]"
                                            rows="2"
                                        ><?= e(
                                            (string) (
                                                $line['description']
                                                ?? ''
                                            )
                                        ) ?></textarea>

                                    </div>

                                    <div class="invoice-line-fields">

                                        <div class="form-group">

                                            <label>
                                                Množství
                                            </label>

                                            <input
                                                type="number"
                                                step="0.001"
                                                min="0"
                                                name="lines[<?= (int) $lineIndex ?>][quantity]"
                                                value="<?= e(
                                                    (string) (
                                                        $line['quantity']
                                                        ?? 1
                                                    )
                                                ) ?>"
                                            >

                                        </div>

                                        <div class="form-group">

                                            <label>
                                                Jednotka
                                            </label>

                                            <select
                                                name="lines[<?= (int) $lineIndex ?>][unit]"
                                            >

                                                <?php foreach (
                                                    $units
                                                    as $unitValue => $unitLabel
                                                ): ?>

                                                    <option
                                                        value="<?= e(
                                                            (string) $unitValue
                                                        ) ?>"
                                                        <?= ($line['unit'] ?? '')
                                                            === $unitValue
                                                            ? 'selected'
                                                            : '' ?>
                                                    >
                                                        <?= e(
                                                            (string) $unitLabel
                                                        ) ?>
                                                    </option>

                                                <?php endforeach; ?>

                                            </select>

                                        </div>

                                        <div class="form-group">

                                            <label>
                                                Cena za
                                            </label>

                                            <select
                                                name="lines[<?= (int) $lineIndex ?>][price_unit]"
                                            >

                                                <?php foreach (
                                                    $priceUnits
                                                    as $priceUnitValue => $priceUnitLabel
                                                ): ?>

                                                    <option
                                                        value="<?= e(
                                                            (string) $priceUnitValue
                                                        ) ?>"
                                                        <?= ($line['price_unit'] ?? '')
                                                            === $priceUnitValue
                                                            ? 'selected'
                                                            : '' ?>
                                                    >
                                                        <?= e(
                                                            (string) $priceUnitLabel
                                                        ) ?>
                                                    </option>

                                                <?php endforeach; ?>

                                            </select>

                                        </div>

                                        <div class="form-group">

                                            <label>
                                                Cena
                                            </label>

                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                name="lines[<?= (int) $lineIndex ?>][unit_price]"
                                                value="<?= e(
                                                    (string) (
                                                        $line['unit_price']
                                                        ?? 0
                                                    )
                                                ) ?>"
                                            >

                                        </div>

                                        <div class="form-group">

                                            <label>
                                                Měna
                                            </label>

                                            <select
                                                name="lines[<?= (int) $lineIndex ?>][currency]"
                                            >
                                                <option
                                                    value="CZK"
                                                    <?= ($line['currency'] ?? 'CZK')
                                                        === 'CZK'
                                                        ? 'selected'
                                                        : '' ?>
                                                >
                                                    CZK
                                                </option>
                                            </select>

                                        </div>

                                        <div class="form-group">

                                            <label>
                                                Celkem
                                            </label>

                                            <output
                                                class="invoice-line-total"
                                            >
                                                <?= e(
                                                    number_format(
                                                        (float) (
                                                            $line['total']
                                                            ?? 0
                                                        ),
                                                        2,
                                                        ',',
                                                        ' '
                                                    )
                                                ) ?>
                                            </output>

                                        </div>

                                        <div class="form-group invoice-line-remove">

                                            <button
                                                type="button"
                                                class="btn btn-secondary js-remove-line"
                                            >
                                                Odebrat
                                            </button>

                                        </div>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                        <button
                            type="button"
                            class="btn btn-secondary"
                            id="add-other-line"
                        >
                            + Přidat ostatní fakturační položku
                        </button>

                    </div>


                    <!-- =================================================
                         CELKEM
                         ================================================= -->

                    <div class="invoice-total">

                        <strong>
                            Celkem
                        </strong>

                        <output id="invoice-total">
                            0,00 Kč
                        </output>

                    </div>

                </fieldset>


                <!-- =================================================
                     POZNÁMKA
                     ================================================= -->

                <fieldset class="invoice-section">

                    <legend>Poznámka</legend>

                    <textarea
                        name="note"
                        rows="4"
                    ><?= e(
                        (string) (
                            $invoice['note'] ?? ''
                        )
                    ) ?></textarea>

                </fieldset>


                <!-- =================================================
                     AKCE
                     ================================================= -->

                <div class="form-actions">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Vytvořit fakturu
                    </button>

                    <a
                        href="<?= Url::to(
                            '/{tenant}/workbench/#main'
                        ) ?>"
                        class="btn btn-secondary"
                    >
                        Zpět
                    </a>

                </div>

            </main>


            <!-- =====================================================
                 PRAVÁ ČÁST – 1/3
                 ===================================================== -->

            <aside class="invoice-reports">

                <fieldset class="invoice-section">

                    <legend>Reporty úkolů</legend>

                    <?php if ($tasks === []): ?>

                        <p>
                            K faktuře nejsou přiřazeny žádné úkoly.
                        </p>

                    <?php else: ?>

                        <p class="text-muted">
                            Reporty úkolu zobrazíte na vyžádání.
                        </p>

                        <div class="task-report-list">

                            <?php foreach ($tasks as $task): ?>

                                <?php
                                $taskId = (int) (
                                    $task['task_id'] ?? 0
                                );
                                ?>

                                <div
                                    class="task-report-item"
                                    data-task-id="<?= $taskId ?>"
                                >

                                    <button
                                        type="button"
                                        class="btn btn-secondary js-load-task-report"
                                        data-task-id="<?= $taskId ?>"
                                        data-report-url="<?= Url::to(
                                            '/{tenant}/ajax/task/' .
                                            $taskId .
                                            '/get-reports'
                                        ) ?>"
                                    >
                                        Zobrazit report
                                    </button>

                                    <div class="task-report-content"></div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                </fieldset>

            </aside>

        </div>

    </form>

</div>


<?php require __DIR__ . '/../layout/footer.php'; ?>