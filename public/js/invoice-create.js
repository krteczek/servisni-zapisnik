(function () {
    'use strict';

    const invoiceForm = document.getElementById('invoice-form');
    let nextLineIndex = Number(invoiceForm.dataset.nextLineIndex);

    const form = document.getElementById('invoice-form');
    const otherLines = document.getElementById('other-invoice-lines');
    const addOtherLineButton =
        document.getElementById('add-other-line');

    function createLine(taskId) {
        const index = nextLineIndex++;

        const wrapper = document.createElement('div');

        wrapper.className = 'invoice-line';

        wrapper.dataset.lineIndex = String(index);

        wrapper.innerHTML = `
            <input
                type="hidden"
                name="lines[${index}][task_id]"
                value="${taskId || ''}"
            >

            <div class="form-group">
                <label>Popis</label>
                <textarea
                    name="lines[${index}][description]"
                    rows="2"
                ></textarea>
            </div>

            <div class="invoice-line-fields">

                <div class="form-group">
                    <label>Množství</label>
                    <input
                        type="number"
                        step="0.001"
                        min="0"
                        name="lines[${index}][quantity]"
                        value="1"
                    >
                </div>

                <div class="form-group">
                    <label>Jednotka</label>
                    <select
                        name="lines[${index}][unit]"
                    >
                        <option value="min">min</option>
                        <option value="km">km</option>
                        <option value="ks" selected>ks</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Cena za</label>
                    <select
                        name="lines[${index}][price_unit]"
                    >
                        <option value="hod">hod</option>
                        <option value="km">km</option>
                        <option value="ks" selected>ks</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Cena</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="lines[${index}][unit_price]"
                        value="0"
                    >
                </div>

                <div class="form-group">
                    <label>Měna</label>
                    <select
                        name="lines[${index}][currency]"
                    >
                        <option value="CZK">CZK</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Celkem</label>
                    <output class="invoice-line-total">
                        0,00
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
        `;

        return wrapper;
    }

    function updateLineTotal(line) {
        const quantity =
            parseFloat(
                line.querySelector(
                    '[name$="[quantity]"]'
                )?.value || '0'
            );

        const unit =
            line.querySelector(
                '[name$="[unit]"]'
            )?.value || '';

        const priceUnit =
            line.querySelector(
                '[name$="[price_unit]"]'
            )?.value || '';

        const unitPrice =
            parseFloat(
                line.querySelector(
                    '[name$="[unit_price]"]'
                )?.value || '0'
            );

        let total = quantity * unitPrice;

        if (
            unit === 'min'
            && priceUnit === 'hod'
        ) {
            total = (quantity / 60) * unitPrice;
        }

        const output =
            line.querySelector('.invoice-line-total');

        if (output === null) {
            return;
        }

        output.textContent =
            total.toLocaleString('cs-CZ', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + ' Kč';
    }

    function updateInvoiceTotal() {
        let total = 0;

        form.querySelectorAll('.invoice-line')
            .forEach(function (line) {
                const quantity =
                    parseFloat(
                        line.querySelector(
                            '[name$="[quantity]"]'
                        )?.value || '0'
                    );

                const unit =
                    line.querySelector(
                        '[name$="[unit]"]'
                    )?.value || '';

                const priceUnit =
                    line.querySelector(
                        '[name$="[price_unit]"]'
                    )?.value || '';

                const unitPrice =
                    parseFloat(
                        line.querySelector(
                            '[name$="[unit_price]"]'
                        )?.value || '0'
                    );

                if (
                    unit === 'min'
                    && priceUnit === 'hod'
                ) {
                    total +=
                        (quantity / 60) * unitPrice;
                } else {
                    total +=
                        quantity * unitPrice;
                }
            });

        const output =
            document.getElementById('invoice-total');

        if (output !== null) {
            output.textContent =
                total.toLocaleString('cs-CZ', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' Kč';
        }
    }

    if (addOtherLineButton !== null) {
        addOtherLineButton.addEventListener(
            'click',
            function () {
                otherLines.appendChild(
                    createLine('')
                );

                updateInvoiceTotal();
            }
        );
    }

    form.addEventListener(
        'click',
        function (event) {

            const addButton =
                event.target.closest(
                    '.js-add-task-line'
                );

            if (addButton !== null) {

                const taskId =
                    addButton.dataset.taskId || '';

                const container =
                    form.querySelector(
                        '[data-task-lines="' +
                        taskId +
                        '"]'
                    );

                if (container !== null) {
                    container.appendChild(
                        createLine(taskId)
                    );
                }

                updateInvoiceTotal();

                return;
            }

            const removeButton =
                event.target.closest(
                    '.js-remove-line'
                );

            if (removeButton !== null) {

                const line =
                    removeButton.closest(
                        '.invoice-line'
                    );

                if (line !== null) {
                    line.remove();
                }

                updateInvoiceTotal();
            }
        }
    );

    form.addEventListener(
        'input',
        function (event) {

            if (
                event.target.matches(
                    '[name$="[quantity]"], [name$="[unit_price]"]'
                )
            ) {
                const line =
                    event.target.closest(
                        '.invoice-line'
                    );

                if (line !== null) {
                    updateLineTotal(line);
                }

                updateInvoiceTotal();
            }
        }
    );

    form.addEventListener(
        'change',
        function (event) {

            if (
                event.target.matches(
                    '[name$="[unit]"], [name$="[price_unit]"]'
                )
            ) {
                const line =
                    event.target.closest(
                        '.invoice-line'
                    );

                if (line !== null) {
                    updateLineTotal(line);
                }

                updateInvoiceTotal();
            }
        }
    );

    form.querySelectorAll('.invoice-line')
        .forEach(updateLineTotal);

    updateInvoiceTotal();


    /*
     * Reporty tasků.
     *
     * URL dodá controller prostřednictvím
     * $view->data['report_url'].
     */
    document.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest(
                    '.js-load-task-report'
                );

            if (button === null) {
                return;
            }

            const url =
                button.dataset.reportUrl || '';

            const taskId =
                button.dataset.taskId || '';

            if (url === '' || taskId === '') {
                return;
            }

            const item =
                button.closest(
                    '.task-report-item'
                );

            if (item === null) {
                return;
            }

            const target =
                item.querySelector(
                    '.task-report-content'
                );

            if (target === null) {
                return;
            }

            button.disabled = true;

            fetch(
                url +
                (url.includes('?') ? '&' : '?') +
                'task_id=' +
                encodeURIComponent(taskId),
                {
                    method: 'GET',
                    headers: {
                        'X-Requested-With':
                            'XMLHttpRequest'
                    }
                }
            )
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error(
                            'HTTP ' + response.status
                        );
                    }

                    return response.text();
                })
                .then(function (html) {
                    target.innerHTML = html;
                    button.textContent =
                        'Skrýt report';
                })
                .catch(function () {
                    target.textContent =
                        'Report se nepodařilo načíst.';
                })
                .finally(function () {
                    button.disabled = false;
                });
        }
    );

})();