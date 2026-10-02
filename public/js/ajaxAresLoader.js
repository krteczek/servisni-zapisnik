(function () {
    'use strict';

    document.addEventListener('click', function (event) {

        const button =
            event.target.closest('[data-ajax-loader]');

        if (button === null) {
            return;
        }

        const sourceName =
            button.dataset.ajaxSource || '';

        const url =
            button.dataset.ajaxUrl || '';

        const messageId =
            button.dataset.ajaxMessage || '';

        if (sourceName === '' || url === '') {
            return;
        }

        const source =
            document.querySelector(
                `[name="${sourceName}"]`
            );

        if (source === null) {
            return;
        }

        const message =
            messageId !== ''
                ? document.getElementById(messageId)
                : null;

        function showMessage(text, type = 'error') {

            if (message === null) {
                return;
            }

            message.textContent = text;
            message.dataset.type = type;
            message.hidden = false;
        }

        function clearMessage() {

            if (message === null) {
                return;
            }

            message.textContent = '';
            message.hidden = true;
            delete message.dataset.type;
        }

        clearMessage();

        const value =
            source.value.trim();

        if (value === '') {
            showMessage(
                'Není zadána hodnota pro načtení údajů.'
            );
            return;
        }

        button.disabled = true;

        const requestUrl =
            url + encodeURIComponent(value);

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
                        result.message ??
                        'Údaje se nepodařilo načíst.'
                    );

                    return;
                }

                if (
                    !result ||
                    result.ok !== true ||
                    !result.data
                ) {
                    showMessage(
                        'Server nevrátil platná data.'
                    );

                    return;
                }

                Object.entries(result.data)
                    .forEach(function ([name, value]) 
                    {

                        const field =
                            document.querySelector(
                                `[data-ajax-field="${name}"]`
                            );

                        if (field === null) {
                            return;
                        }

                        if (
                            field instanceof HTMLInputElement ||
                            field instanceof HTMLTextAreaElement ||
                            field instanceof HTMLSelectElement
                        ) {
                            field.value = value ?? '';
                            return;
                        }

                        field.textContent = value ?? '';                    
                    });

                updateDiff();
                showMessage(
                    'Údaje byly načteny.',
                    'success'
                );
            })
            .catch(function (error) {

                showMessage(
                    error.message ||
                    'Údaje se nepodařilo načíst.'
                );
            })
            .finally(function () {
                
                const aresData =
                document.querySelector('.company-ares-data');

                if (aresData !== null) {
                    aresData.hidden = false;
                }
                button.disabled = false;
            });
    });

})();