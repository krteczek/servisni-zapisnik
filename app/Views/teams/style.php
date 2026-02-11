<?php
$css = <<<CSS
<style>

/* =====================================================
   VARIABLES
   ===================================================== */

:root {
    --ui-border: #ddd;
    --ui-bg-soft: #f9f9f9;
    --ui-text-muted: #555;
}

/* =====================================================
   TEAM INTRO
   ===================================================== */

.team-intro {
    --team-color: {$team['color']};

    margin: 24px 0;
    padding: 12px 16px;
    background: var(--ui-bg-soft);
    border-left: 4px solid var(--team-color);
    border-radius: 4px;
}

.team-intro p {
    margin: 0 0 6px;
    font-weight: 600;
}

.team-intro ul {
    margin: 0;
    padding-left: 18px;
}

.team-intro li {
    margin: 2px 0;
}

/* =====================================================
   TEAMS LIST
   ===================================================== */

.teams-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

/* =====================================================
   TEAM ITEM
   ===================================================== */

.team-item {
    --team-color: inherit;

    position: relative;
    background: #fff;
    border: 1px solid var(--ui-border);
    border-radius: 6px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: .75rem;
}

/* barevný overlay */
.team-item::before {
    content: '';
    position: absolute;
    inset: 0;
    background: var(--team-color);
    opacity: .06;
    border-radius: inherit;
    pointer-events: none;
}

/* levý akcent */
.team-item::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--team-color);
    border-radius: 6px 0 0 6px;
}

/* obsah nad overlayem */
.team-item > * {
    position: relative;
    z-index: 1;
}

.team-item.is-inactive {
    filter: grayscale(.6);
    opacity: .65;
}

/* =====================================================
   TEAM HEADER
   ===================================================== */

.team-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.team-title {
    display: flex;
    align-items: center;
    gap: .5rem;
}

.team-name {
    font-size: 1.05rem;
    color: #222;
}

.team-color {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--team-color);
    flex-shrink: 0;
}

/* =====================================================
   TEAM MEMBERS
   ===================================================== */

.team-members {
    display: flex;
    flex-direction: column;
    gap: .2rem;
    font-size: .85rem;
}

.member-line {
    padding-left: .5rem;
    border-left: 3px solid transparent;
}

.member-line.role-leader {
    border-color: #fbc02d;
    font-weight: 700;
}

.member-line.role-member {
    border-color: #26a69a;
}

.member-line.role-guest {
    border-color: #bdbdbd;
    opacity: .75;
}

/* =====================================================
   TEAM ACTIONS
   ===================================================== */

.team-actions {
    margin-top: auto;
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
}

.action-link {
    font-size: .85rem;
    padding: .35rem .6rem;
    border-radius: 4px;
    text-decoration: none;
    background: #f5f5f5;
    color: #333;
}

.action-link:hover {
    background: #e0e0e0;
}

/* =====================================================
   FORM TABLE
   ===================================================== */

.form-table {
    width: 100%;
    max-width: 900px;
    margin: 1.5rem auto;
    border-collapse: separate;
    border-spacing: 0 .75rem;
}

.form-table th {
    width: 180px;
    padding-top: .4rem;
    text-align: left;
    font-size: .85rem;
    font-weight: 600;
    color: #444;
    vertical-align: top;
}

.form-table input,
.form-table textarea,
.form-table select {
    width: 100%;
    padding: .45rem .55rem;
    font-size: .9rem;
    border: 1px solid #ccc;
    border-radius: 6px;
}

.form-actions {
    padding-top: 1rem;
    display: flex;
    gap: .5rem;
}

.req {
    color: #c62828;
}

/* =====================================================
   USER DUAL LIST
   ===================================================== */

.user-dual-list {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    max-width: 900px;
    margin: 1.5rem auto;
}

.user-list {
    background: #fff;
    border: 1px solid var(--ui-border);
    border-radius: 6px;
    padding: .75rem;
    display: flex;
    flex-direction: column;
    box-shadow: 0 1px 2px rgba(0,0,0,.04);
}

.user-list:first-child {
    border-left: 4px solid var(--team-color);
}

.user-list:last-child {
    border-left: 4px solid #90caf9;
}

.user-list h3 {
    margin-bottom: .5rem;
    padding-bottom: .4rem;
    font-size: .9rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
    color: #444;
    border-bottom: 2px solid #e0e0e0;
}

/* =====================================================
   USER ROW
   ===================================================== */

.user-row {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .4rem .25rem;
    border-bottom: 1px solid #eee;
    border-radius: 4px;
}

.user-row:last-child {
    border-bottom: none;
}

.user-row:hover {
    background: #f7f7f7;
}

.user-name {
    flex: 1;
    font-size: .85rem;
    color: #222;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-name:hover {
    white-space: normal;
}

.empty-list {
    font-size: .8rem;
    font-style: italic;
    color: #777;
    padding: .5rem 0;
}

/* =====================================================
   ROLE SELECT & ACTIONS
   ===================================================== */

.role-select {
    font-size: .75rem;
    padding: .2rem .3rem;
    background: #fafafa;
    border: 1px solid #ccc;
    cursor: pointer;
}

.role-select:hover {
    background: #fff;
}

.remove-form .btn {
    font-size: 1rem;
    padding: .2rem .4rem;
    opacity: .65;
}

.remove-form .btn:hover {
    opacity: 1;
}

.user-list .btn-secondary {
    font-size: .75rem;
    padding: .2rem .4rem;
}

/* =====================================================
   HINT
   ===================================================== */

.hint {
    max-width: 900px;
    margin: .5rem auto 1rem;
    padding: .5rem .75rem;
    font-size: .8rem;
    color: var(--ui-text-muted);
    background: var(--ui-bg-soft);
    border-left: 4px solid var(--team-color);
    border-radius: 4px;
}

/* =====================================================
   MOBILE
   ===================================================== */

@media (max-width: 700px) {
    .user-dual-list {
        grid-template-columns: 1fr;
    }
}

</style>
CSS;
