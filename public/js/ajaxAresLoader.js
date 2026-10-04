(function () {
    'use strict';

    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-ajax-loader]');

        if (button === null) {
            return;
        }

        const sourceName = button.dataset.ajaxSource || '';
        const url = button.dataset.ajaxUrl || '';
        const messageId = button.dataset.ajaxMessage || '';

        if (sourceName === '' || url === '') {
            return;
        }

        const source = document.querySelector(
            `[name="${sourceName}"]`
        );

        if (source === null) {
            return;
        }

        const message = messageId !== ''
            ? document.getElementById(messageId)
            : null;

        function showMessage(text, type) {
            if (message === null) {
                return;
            }

            message.innerHTML = text;
            message.dataset.type = type || 'error';
            message.hidden = false;
        }

        function clearMessage() {
            if (message === null) {
                return;
            }

            message.innerHTML = '';
            message.hidden = true;
            delete message.dataset.type;
        }

        clearMessage();

        const value = source.value.trim();

        if (value === '') {
            showMessage(
                'Není zadána hodnota pro načtení údajů.'
            );

            return;
        }

        button.disabled = true;

        const requestUrl = url + encodeURIComponent(value);

        fetch(requestUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                return response.json()
                    .then(function (result) {
                        return {
                            response: response,
                            result: result
                        };
                    })
                    .catch(function () {
                        throw new Error(
                            'Server vrátil neplatnou JSON odpověď.'
                        );
                    });
            })
            .then(function (data) {
                const response = data.response;
                const result = data.result;

                if (!response.ok) {
                    showMessage(
                        result.message ?? 'Údaje se nepodařilo načíst.'
                    );

                    return;
                }

                if (
                    !result
                    || result.ok !== true
                    || !result.data
                ) {
                    showMessage(
                        'Server nevrátil platná data.'
                    );

                    return;
                }

                const comparison =
                    document.getElementById(
                        'contact-ares-comparison'
                    );

                /*
                 * Editace kontaktu:
                 * ARES data pouze zobrazíme v porovnávací tabulce.
                 */
                if (comparison !== null) {
                    updateContactAresComparison(result.data);

                    comparison.hidden = false;

                    showMessage(
                        'Údaje byly načteny. Vyberte údaje, které chcete převzít.',
                        'success'
                    );

                    return;
                }

                /*
                 * Nový kontakt:
                 * zde zatím zachováváme původní chování –
                 * ARES hodnoty se doplní přímo do formuláře.
                 */
                Object.entries(result.data).forEach(
                    function ([name, value]) {
                        const field = document.querySelector(
                            `[data-ajax-field="${name}"]`
                        );

                        if (field === null) {
                            return;
                        }

                        if (
                            field instanceof HTMLInputElement
                            || field instanceof HTMLTextAreaElement
                            || field instanceof HTMLSelectElement
                        ) {
                            field.value = value ?? '';

                            return;
                        }

                        field.textContent = value ?? '';
                    }
                );

                if (typeof updateDiff === 'function') {
                    updateDiff();
                }

                showMessage(
                    'Údaje byly načteny.',
                    'success'
                );
            })
            .catch(function (error) {
                showMessage(
                    error.message
                    || 'Údaje se nepodařilo načíst.'
                );
            })
            .finally(function () {
                button.disabled = false;
            });
    });

    /**
     * Naplní porovnávací tabulku ARES údajů.
     *
     * @param {Object} data
     */
    function updateContactAresComparison(data) {
        const rows = document.querySelectorAll(
            '[data-ares-row]'
        );

        rows.forEach(function (row) {
            const field = row.dataset.aresRow || '';

            if (field === '') {
                return;
            }

            const dbElement = row.querySelector(
                '[data-db-field]'
            );

            const aresElement = row.querySelector(
                '[data-ares-field]'
            );

            const selectElement = row.querySelector(
                '[data-ares-select]'
            );

            if (
                dbElement === null
                || aresElement === null
                || selectElement === null
            ) {
                return;
            }

            const dbValue = normalizeValue(
                dbElement.textContent
            );

            const aresValue = normalizeValue(
                data[field] ?? ''
            );

            aresElement.textContent =
                data[field] ?? '';

            const checkbox =
                selectElement.querySelector(
                    'input[type="checkbox"]'
                );

            if (checkbox === null) {
                return;
            }

            /*
             * Stejná hodnota:
             * není co převzít, checkbox nezobrazujeme.
             */
            if (dbValue === aresValue) {
                selectElement.hidden = true;
                checkbox.checked = false;

                dbElement.classList.remove('diff-old');
                aresElement.classList.remove('diff-new');

                return;
            }

            /*
             * Rozdílná hodnota:
             * uživateli nabídneme převzetí z ARES.
             */
                selectElement.hidden = false;
                checkbox.checked = false;

                dbElement.classList.add('diff-old');
                aresElement.classList.add('diff-new');        
            
        });
    }

    /**
     * Sjednotí hodnotu pro porovnání.
     *
     * @param {*} value
     * @returns {string}
     */
    function normalizeValue(value) {
        if (value === null || value === undefined) {
            return '';
        }

        return String(value)
            .replace(/\r\n/g, '\n')
            .trim();
    }
})();