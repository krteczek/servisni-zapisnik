<?php
$css = <<<CSS
<style>

/* =========================
   TEAMS – LIST
   ========================= */

.teams-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.team-item {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: .75rem;
}

.team-item.is-inactive {
    opacity: .55;
}

/* =========================
   HEADER
   ========================= */

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

/* =========================
   MEMBERS
   ========================= */

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

/* ROLE VISUAL */
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

/* =========================
   ACTIONS
   ========================= */

.team-actions {
    margin-top: auto;
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
}

.action-link {
    font-size: .85rem;
    text-decoration: none;
    padding: .35rem .6rem;
    border-radius: 4px;
    background: #f5f5f5;
    color: #333;
}

.action-link:hover {
    background: #e0e0e0;
}

.action-link.toggle {
    background: #fafafa;
}

</style>
CSS;
