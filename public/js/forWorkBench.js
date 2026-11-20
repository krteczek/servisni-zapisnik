/**
 * Workbench – obecné přepínání tabů libovolné hloubky.
 * Struktura:
 *   .tab-level
 *     .tab-button[data-target]
 *     .tab-content[id]
 *       ... (volitelně další .tab-level uvnitř)
 */
(function () {
    'use strict';

    function activateTab(targetId, { openFirstChild = true, updateUrl = true } = {}) {
        const target = document.getElementById(targetId);
        if (!target) return;

        const parent = target.parentElement;
        if (!parent) return;

        parent.querySelectorAll(':scope > .tab-content').forEach(el => {
            el.hidden = el !== target;
        });

        parent.querySelectorAll(':scope > .tab-button').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.target === targetId);
        });

        if (openFirstChild) {
            const firstChild = target.querySelector(':scope > .tab-level > .tab-content');
            if (firstChild) activateTab(firstChild.id, { updateUrl: false });
        }

        if (updateUrl) {
            history.replaceState(null, '', '#' + targetId);
        }
    }

    function openFromUrl() {
        const params = new URLSearchParams(location.search);
        const targetId = params.get('open') || location.hash.replace(/^#/, '');
        if (!targetId) return;

        const target = document.getElementById(targetId);
        if (!target || !target.classList.contains('tab-content')) return;

        const chain = [];
        let el = target;
        while (el && el.classList.contains('tab-content')) {
            chain.unshift(el.id);
            el = el.parentElement?.closest('.tab-content');
        }

        chain.forEach((id, i) => {
            activateTab(id, {
                openFirstChild: i === chain.length - 1,
                updateUrl: false,
            });
        });

        if (params.get('open')) {
            history.replaceState(null, '', '#' + targetId);
        }
    }

    function bindClickHandlers() {
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                activateTab(button.dataset.target);
            });
        });
    }

    function init() {
        bindClickHandlers();
        openFromUrl();
    }

    document.addEventListener('DOMContentLoaded', init);
    window.addEventListener('pageshow', (e) => {
        if (e.persisted) openFromUrl();
    });
})();