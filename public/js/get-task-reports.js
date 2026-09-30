/**
 * Slouží k načítání reportů úkolů
 */

(function () {
    'use strict';

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

            const url =
                button.dataset.reportUrl || '';

            if (url === '') {
                return;
            }

            /*
             * Pokud už byl report načten,
             * pouze ho zobrazíme nebo schováme.
             */
            if (
                item.dataset.reportLoaded === '1'
            ) {
                const isHidden =
                    target.hidden;

                target.hidden = !isHidden;

                button.textContent =
                    target.hidden
                        ? 'Zobrazit report'
                        : 'Skrýt report';

                return;
            }

            /*
             * Načtení reportů z AJAX endpointu.
             */
            button.disabled = true;

            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function (response) {

                    if (!response.ok) {
                        throw new Error(
                            'HTTP ' + response.status
                        );
                    }

                    return response.json();
                })
                .then(function (result) {

                    if (
                        !result ||
                        result.ok !== true ||
                        !Array.isArray(result.data)
                    ) {
                        throw new Error(
                            'Neplatná odpověď serveru.'
                        );
                    }

                    renderReports(
                        target,
                        result.data
                    );

                    item.dataset.reportLoaded = '1';
                    target.hidden = false;

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


    /**
     * Vykreslí všechny reporty úkolu.
     */
    function renderReports(target, reports) {

        target.innerHTML = '';

        if (reports.length === 0) {
            const empty =
                document.createElement('p');

            empty.className =
                'text-muted';

            empty.textContent =
                'K tomuto úkolu nejsou žádné reporty.';

            target.appendChild(empty);

            return;
        }

        reports.forEach(function (report) {

            target.appendChild(
                createReportElement(report)
            );
        });
    }


    /**
     * Vytvoří jeden report.
     */
    function createReportElement(report) {

        const wrapper =
            document.createElement('div');

        wrapper.className =
            'task-report';


        /*
         * Datum a autor.
         */
        const meta =
            document.createElement('div');

        meta.className =
            'task-report-meta';

        const author =
            buildFullName(
                report.created_by_first_name,
                report.created_by_last_name
            ) + ' napsal: ';

        meta.textContent =
            formatDateTime(report.created_at) +
            ' · ' +
            (author || 'Neznámý uživatel');


        /*
         * Poznámka — hlavní obsah reportu.
         */
        const note =
            document.createElement('div');

        note.className =
            'task-report-note';

        note.innerHTML = report.note;


        /*
         * Čas a kilometry.
         */
        const summary =
            document.createElement('div');

        summary.className =
            'task-report-summary';

        const minutes =
            Number(report.minutes_spent || 0);

        const kilometers =
            Number(report.kilometers || 0);

        summary.textContent =
            formatMinutes(minutes) +
            ' · ' +
            formatKilometers(kilometers);


        /*
         * Účastníci.
         */
        const participants =
            document.createElement('div');

        participants.className =
            'task-report-participants';

        const participantList =
            Array.isArray(report.participants)
                ? report.participants
                : [];

        participantList.forEach(
            function (participant) {

                participants.appendChild(
                    createParticipantElement(
                        participant
                    )
                );
            }
        );


        wrapper.appendChild(meta);
        wrapper.appendChild(note);
        wrapper.appendChild(summary);

        if (participantList.length > 0) {
            wrapper.appendChild(
                participants
            );
        }

        return wrapper;
    }


    /**
     * Vytvoří řádek účastníka.
     */
    function createParticipantElement(
        participant
    ) {

        const row =
            document.createElement('div');

        const name =
            buildFullName(
                participant.first_name,
                participant.last_name
            );

        const minutes =
            Number(
                participant.minutes_spent || 0
            );

        const nameElement =
            document.createElement('span');

        nameElement.textContent =
            (name || 'Neznámý pracovník') + ': ';

        const timeElement =
            document.createElement('span');

        timeElement.textContent =
            formatMinutes(minutes);

        row.appendChild(nameElement);
        row.appendChild(timeElement);

        return row;
    }


    /**
     * Sestaví celé jméno.
     */
    function buildFullName(firstName, lastName) {

        return (
            String(firstName || '') +
            ' ' +
            String(lastName || '')
        ).trim();
    }


    /**
     * Formát data z databáze.
     *
     * Vstup:
     * 2026-09-30 07:09:27
     *
     * Výstup:
     * 30. 9. 2026 07:09
     */
    function formatDateTime(value) {

        const text =
            String(value || '');

        const match =
            text.match(
                /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/
            );

        if (match === null) {
            return text;
        }

        return (
            Number(match[3]) +
            '. ' +
            Number(match[2]) +
            '. ' +
            match[1] +
            ' ' +
            match[4] +
            ':' +
            match[5]
        );
    }


    /**
     * Formát minut.
     *
     * 1320  -> 22 h
     * 300   -> 5 h
     * 330   -> 5 h 30 min
     * -80   -> -1 h 20 min
     */
    function formatMinutes(minutes) {

        minutes =
            Number.isFinite(minutes)
                ? Math.round(minutes)
                : 0;

        if (minutes === 0) {
            return '0 h';
        }

        const sign =
            minutes < 0
                ? '-'
                : '';

        const absoluteMinutes =
            Math.abs(minutes);

        const hours =
            Math.floor(absoluteMinutes / 60);

        const rest =
            absoluteMinutes % 60;

        if (rest === 0) {
            return sign + hours + ' h';
        }

        return (
            sign +
            hours +
            ' h ' +
            rest +
            ' min'
        );
    }


    /**
     * Formát kilometrů.
     */
    function formatKilometers(kilometers) {

        kilometers =
            Number.isFinite(kilometers)
                ? kilometers
                : 0;

        return (
            kilometers.toLocaleString(
                'cs-CZ',
                {
                    maximumFractionDigits: 2
                }
            ) +
            ' km'
        );
    }

})();
