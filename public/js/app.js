/**
 * Společné UI chování – confirm formulářů, toggleAll checkbox, user time boxy.
 */
(function () {
    'use strict';

    function initConfirmForms() {
        document.querySelectorAll('form[data-confirm]').forEach(form => {
            form.addEventListener('submit', e => {
                if (!confirm(form.dataset.confirm)) {
                    e.preventDefault();
                }
            });
        });
    }

    function initToggleAll() {
        const toggle = document.getElementById('toggleAll');
        if (!toggle) return;

        toggle.addEventListener('change', e => {
            document.querySelectorAll('input[name="ids[]"]')
                .forEach(cb => cb.checked = e.target.checked);
        });
    }

    function initUserCheckboxes() {
        document.querySelectorAll('.user-checkbox').forEach(cb => {
            const userId = cb.dataset.userId;
            const timeBox = document.getElementById(`time-${userId}`);

            if (!timeBox) return;

            // inicializace (když je už checked po reloadu)
            timeBox.style.display = cb.checked ? 'block' : 'none';

            cb.addEventListener('change', () => {
                timeBox.style.display = cb.checked ? 'block' : 'none';
            });
        });
    }

    function init() {
        initConfirmForms();
        initToggleAll();
        initUserCheckboxes();
    }

    document.addEventListener('DOMContentLoaded', init);
})();