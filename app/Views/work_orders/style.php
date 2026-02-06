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
';



$css .= '
<style type="text/css">
/* =========================
   WORK ORDER – DETAIL
   ========================= */

.wo-detail {
    max-width: 900px;
    margin: 1.5rem auto;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.wo-detail-header {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.wo-detail-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #222;
}

.wo-detail-meta {
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
}

/* =========================
   SECTIONS
   ========================= */

.wo-section {
    border-top: 1px solid #eee;
    padding-top: 1rem;
}

.wo-section h3 {
    font-size: .9rem;
    font-weight: 700;
    margin-bottom: .5rem;
    color: #444;
}

/* =========================
   ACTIONS
   ========================= */

.wo-actions-detail {
    display: grid;
    gap: .75rem;
}

.wo-action-box {
    border: 1px dashed #ccc;
    border-radius: 6px;
    padding: .75rem;
    background: #fafafa;
}

.wo-action-box p {
    margin-bottom: .5rem;
    font-size: .85rem;
    color: #333;
}

.wo-action-box form {
    display: flex;
    justify-content: flex-end;
}

/* buttons reuse bootstrap-ish classes */

/* =========================
   DETAIL – INFO BLOCKS
   ========================= */

.wo-info-main {
    background: #f9f9f9;
    border: 1px solid #eee;
    border-radius: 6px;
    padding: .75rem;
    margin-bottom: .75rem;
    font-size: .9rem;
}

.wo-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: .5rem;
}

.wo-info-box {
    background: #fafafa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: .5rem .75rem;
    display: flex;
    flex-direction: column;
    gap: .15rem;
}

.wo-info-box .label {
    font-size: .65rem;
    font-weight: 700;
    color: #777;
    text-transform: uppercase;
}

.wo-info-box .value {
    font-size: .85rem;
    color: #222;
}

.btn-success {
    background-color: #2e7d32;   /* deep green */
    border-color: #2e7d32;
    color: #fff;
}

.btn-success:hover {
    background-color: #388e3c;
    border-color: #388e3c;
}

.btn-danger {
    background-color: #c62828;   /* deep red */
    border-color: #c62828;
    color: #fff;
}

.btn-danger:hover {
    background-color: #d32f2f;
    border-color: #d32f2f;
}
.btn-success {
    background-color: #3a6f4f;
    border-color: #3a6f4f;
}

.btn-danger {
    background-color: #8b2f2f;
    border-color: #8b2f2f;
}
.btn-danger,
.btn-success {
    font-weight: 800;
    letter-spacing: .4px;
}

.btn {
    border-radius: 6px;
}
</style>
';

$css .= '
<style type="text/css">
/* =========================
   FORM – WORK ORDER CREATE
   ========================= */

.form-table {
    width: 100%;
    max-width: 900px;
    margin: 1.5rem auto;
    border-collapse: separate;
    border-spacing: 0 .75rem;
}

.form-table th {
    text-align: left;
    font-weight: 600;
    font-size: .85rem;
    color: #444;
    vertical-align: top;
    padding-top: .4rem;
    width: 180px;
}

.form-table td {
    width: auto;
}

.form-table input,
.form-table textarea,
.form-table select {
    width: 100%;
    padding: .45rem .55rem;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: .9rem;
}

.form-table textarea {
    resize: vertical;
}

/* hlavní pole */
.form-main input,
.form-main textarea {
    font-size: 1rem;
}

/* akce */
.form-actions {
    padding-top: 1rem;
    display: flex;
    gap: .5rem;
}

/* drobná poznámka povinnosti */
.req {
    color: #c62828;
}

/* =========================
   MOBILE
   ========================= */

@media (max-width: 700px) {

    .form-table {
        display: block;
        width: 100%;
        border-spacing: 0;
    }

    .form-table tr {
        display: block;
        margin-bottom: 1rem;
    }

    .form-table th,
    .form-table td {
        display: block;
        width: 100%;
    }

    .form-table input,
    .form-table select,
    .form-table textarea {
        width: 100%;
        box-sizing: border-box;
    }
}

</style>
';
$css .= '
<style type="text/css">
<!-- -->
.task-box {
    border: 1px solid #ddd;
    padding: 12px;
    margin-bottom: 10px;
    border-radius: 6px;
}

.task-header {
    display: flex;
    justify-content: space-between;
}

.task-stats span {
    margin-right: 10px;
    font-size: 0.9em;
}

.task-actions {
    margin-top: 8px;
}

/* =========================
   TASK – CREATE FORM
   ========================= */

.task-form {
    max-width: 900px;
    margin: 1.5rem auto;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 1.25rem;
}

.task-form h2 {
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: #222;
}

.task-form .form-group {
    margin-bottom: .9rem;
}

.task-form label {
    display: block;
    font-size: .8rem;
    font-weight: 700;
    margin-bottom: .25rem;
    color: #444;
}

.task-form input,
.task-form textarea,
.task-form select {
    width: 100%;
    padding: .45rem .55rem;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: .9rem;
}

.task-form textarea {
    resize: vertical;
    min-height: 90px;
}

.task-form .hint {
    font-size: .7rem;
    color: #777;
    margin-top: .2rem;
}

/* checkbox */
.task-form .checkbox {
    display: flex;
    align-items: center;
    gap: .4rem;
    font-size: .8rem;
}

/* actions */
.task-form .form-actions {
    margin-top: 1.25rem;
    display: flex;
    gap: .5rem;
    justify-content: flex-end;
}

/* =========================
   TASK – VISUAL CONTEXT
   ========================= */

.task-form-context {
    font-size: .75rem;
    color: #666;
    margin-bottom: .75rem;
}

/* =========================
   MOBILE
   ========================= */

@media (max-width: 700px) {
    .task-form {
        margin: 1rem;
        padding: 1rem;
    }
}

</style>


';

?>