/**
 * Workbench – přepínání main/sub tabů + obnova stavu z URL.
 * Vyžaduje: .main-tab-button[data-target], .main-tab-content,
 *           .sub-tab-button[data-target], .sub-tab-content
 */
(function () {
    'use strict';

    function activateMainTab(targetId) {
        document.querySelectorAll('.main-tab-content').forEach(s => s.hidden = true);
        document.querySelectorAll('.main-tab-button').forEach(b => b.classList.remove('active'));

        const target = document.getElementById(targetId);
        if (target) target.hidden = false;

        const btn = document.querySelector(`.main-tab-button[data-target="${targetId}"]`);
        if (btn) btn.classList.add('active');
    }

    function activateSubTab(targetId) {
        document.querySelectorAll('.sub-tab-content').forEach(s => s.hidden = true);
        document.querySelectorAll('.sub-tab-button').forEach(b => b.classList.remove('active'));

        const target = document.getElementById(targetId);
        if (target) target.hidden = false;

        const btn = document.querySelector(`.sub-tab-button[data-target="${targetId}"]`);
        if (btn) btn.classList.add('active');
    }

    function openFromUrl() {
        const params = new URLSearchParams(location.search);
        const fromQuery = params.get('open');
        const fromHash  = location.hash.replace(/^#/, '');
        const targetId  = fromQuery || fromHash;

        if (!targetId) return;

        const target = document.getElementById(targetId);
        if (!target) return;

        if (target.classList.contains('sub-tab-content')) {
            const parent = target.closest('.main-tab-content');
            if (parent) activateMainTab(parent.id);
            activateSubTab(targetId);
        } else if (target.classList.contains('main-tab-content')) {
            activateMainTab(targetId);
        }

        // Ukliď ?open= z URL
        if (fromQuery) {
            history.replaceState(null, '', '#' + targetId);
        }
    }

    function bindClickHandlers() {
        document.querySelectorAll('.main-tab-button').forEach(button => {
            button.addEventListener('click', () => {
                const targetId = button.dataset.target;
                activateMainTab(targetId);
                history.replaceState(null, '', '#' + targetId);
            });
        });

        document.querySelectorAll('.sub-tab-button').forEach(button => {
            button.addEventListener('click', () => {
                const targetId = button.dataset.target;
                activateSubTab(targetId);
                history.replaceState(null, '', '#' + targetId);
            });
        });
    }

    function init() {
        bindClickHandlers();
        openFromUrl();
    }

    document.addEventListener('DOMContentLoaded', init);

    // bfcache – zpět v prohlížeči
    window.addEventListener('pageshow', (e) => {
        if (e.persisted) openFromUrl();
    });
})();