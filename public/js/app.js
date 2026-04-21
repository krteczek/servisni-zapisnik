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

});

document.querySelectorAll('.js-auto-submit').forEach(el => {
    el.addEventListener('change', () => {
        el.form.submit();
    });
});