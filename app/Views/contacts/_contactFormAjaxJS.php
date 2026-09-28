<script>
document.addEventListener('DOMContentLoaded', () => {
    const icoInput = document.querySelector('[name="ico"]');
    const loadButton = document.querySelector('[data-ares-load]');
    const message = document.querySelector('[data-ares-message]');

    if (!icoInput || !loadButton) {
        return;
    }

    function showMessage(text, type = 'error') {
        if (!message) {
            return;
        }

        message.textContent = text;
        message.dataset.type = type;
        message.hidden = false;
    }

    function clearMessage() {
        if (!message) {
            return;
        }

        message.textContent = '';
        message.hidden = true;
        delete message.dataset.type;
    }

    function setValue(name, value) {
        const field = document.querySelector(`[name="${name}"]`);

        if (!field) {
            return;
        }

        field.value = value ?? '';
    }

    function normalizeIco(value) {
        value = value.trim();

        if (value.length < 8) {
            value = value.padStart(8, '0');
        }

        return value;
    }

    loadButton.addEventListener('click', async () => {
        clearMessage();

        const ico = normalizeIco(icoInput.value);

        icoInput.value = ico;

        if (!/^\d{8}$/.test(ico)) {
            showMessage('IČO musí obsahovat 8 číslic.');
            return;
        }

        loadButton.disabled = true;

        try {
            const url = loadButton.dataset.aresUrl + encodeURIComponent(ico);

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            let result;

            try {
                result = await response.json();
            } catch {
                showMessage('Server vrátil neplatnou odpověď.');
                return;
            }

            if (!response.ok) {
                if (result.error === 'invalid_ico') {
                    showMessage(
                        result.message ??
                        'IČO nebylo ověřeno. Zkontrolujte zadané IČO.'
                    );
                    return;
                }

                if (result.error === 'ares_error') {
                    showMessage(
                        result.message ??
                        'Údaje se momentálně nepodařilo ověřit. Zkuste to později nebo je zadejte ručně.'
                    );
                    return;
                }

                if (response.status === 403) {
                    showMessage(
                        'Platnost formuláře vypršela. Načtěte stránku znovu a opakujte ověření.'
                    );
                    return;
                }

                showMessage('Údaje se nepodařilo načíst.');
                return;
            }

            if (result.ok !== true || !result.data) {
                showMessage('ARES nevrátil údaje o subjektu.');
                return;
            }

            const data = result.data;

            setValue('official_name', data.officialName);
            setValue('dic', data.dic);

            setValue('street', data.street);
            setValue('house_number', data.houseNumber);
            setValue('orientation_number', data.orientationNumber);
            setValue('city_part', data.cityPart);
            setValue('city', data.city);
            setValue('postal_code', data.postalCode);
            setValue('country_code', data.countryCode);

            setValue('delivery_address_1', data.deliveryAddress1);
            setValue('delivery_address_2', data.deliveryAddress2);
            setValue('delivery_address_3', data.deliveryAddress3);

            showMessage('Údaje byly načteny z ARES.', 'success');

        } catch {
            showMessage(
                'Údaje se momentálně nepodařilo ověřit. Zkontrolujte připojení nebo to zkuste později.'
            );
        } finally {
            loadButton.disabled = false;
        }
    });
});
</script>