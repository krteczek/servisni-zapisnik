function updateDiff() {
    document.querySelectorAll('tr').forEach(function (row) {

        const oldFields =
            row.querySelectorAll('[data-db-field]');

        const newFields =
            row.querySelectorAll('[data-ajax-field]');

        if (
            oldFields.length === 0 ||
            oldFields.length !== newFields.length
        ) {
            return;
        }

        const oldCell =
            oldFields[0].closest('td');

        const newCell =
            newFields[0].closest('td');

        if (oldCell === null || newCell === null) {
            return;
        }

        oldCell.classList.remove('diff-old');
        newCell.classList.remove('diff-new');

        let different = false;

        oldFields.forEach(function (oldField, index) {
            const oldValue =
                oldField.textContent.trim();

            const newValue =
                newFields[index].textContent.trim();

            if (oldValue !== newValue) {
                different = true;
            }
        });

        if (different) {
            oldCell.classList.add('diff-old');
            newCell.classList.add('diff-new');
        }
    });
}