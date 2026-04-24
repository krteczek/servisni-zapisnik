document.addEventListener('DOMContentLoaded', () => {

    // confirm formuláře
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', e => {
            if (!confirm(form.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // toggleAll checkbox
    const toggle = document.getElementById('toggleAll');
    if (toggle) {
        toggle.addEventListener('change', e => {
            document.querySelectorAll('input[name="ids[]"]')
                .forEach(cb => cb.checked = e.target.checked);
        });
    }

    // 🔥 TOHLE TAM CHYBĚLO
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

});