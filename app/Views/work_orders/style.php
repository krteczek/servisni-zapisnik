<?php
declare(strict_types=1);

$css = '
<style type="text/css">
/* =========================
   WORK ORDERS – BASE
   ========================= */

.work-orders {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.work-order-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: .75rem;
}

/* =========================
   HEADER
   ========================= */

.wo-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: .5rem;
}

.wo-title {
    font-size: 1.05rem;
    font-weight: 600;
    color: #222;
    text-decoration: none;
    line-height: 1.3;
}

.wo-title:hover {
    text-decoration: underline;
}

/* =========================
   PRIORITY
   ========================= */

.wo-priority {
    font-size: .7rem;
    font-weight: 700;
    padding: .25rem .5rem;
    border-radius: 4px;
    white-space: nowrap;
}

.priority-low {
    background: #e7f4ea;
    color: #2e7d32;
}

.priority-medium {
    background: #fff4e5;
    color: #a66b00;
}

.priority-high {
    background: #fdecea;
    color: #c62828;
}

/* =========================
   META / STATUS
   ========================= */

.wo-meta {
    display: flex;
    gap: .5rem;
}

.wo-status {
    font-size: .7rem;
    font-weight: 700;
    padding: .25rem .5rem;
    border-radius: 4px;
}

.status-open {
    background: #e3f2fd;
    color: #1565c0;
}

.status-closed {
    background: #e8f5e9;
    color: #2e7d32;
}

.status-cancelled {
    background: #eeeeee;
    color: #555;
}

/* =========================
   ACTIONS
   ========================= */

.wo-actions {
    margin-top: auto;
    display: flex;
    justify-content: flex-end;
    gap: .25rem;
}

.wo-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 1.1rem;
    background: #f5f5f5;
}

.wo-action:hover {
    background: #e0e0e0;
}

/* =========================
   MOBILE
   ========================= */

@media (max-width: 600px) {
    .work-orders {
        grid-template-columns: 1fr;
    }
}
</style>
'
?>