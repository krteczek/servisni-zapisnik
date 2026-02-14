<?php
$csse = <<<CSS
<style>
/* =====================================================
   USERS LIST
   ===================================================== */

.users-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1rem;
    margin-top: 1rem;
}

.users-empty {
    margin: 2rem 0;
    padding: 1rem;
    background: #f9f9f9;
    border-left: 4px solid #ccc;
    border-radius: 4px;
}

/* =====================================================
   USER CARD
   ===================================================== */

.user-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: .75rem;
}

.user-card:hover {
    box-shadow: 0 4px 10px rgba(0, 0, 0, .06);
}

.user-card.is-inactive {
    filter: grayscale(.6);
    opacity: .7;
}

/* =====================================================
   HEADER
   ===================================================== */

.user-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.user-name {
    font-size: 1.05rem;
    color: #222;
}

/* =====================================================
   BADGE
   ===================================================== */

.user-badge {
    font-size: .75rem;
    padding: .25rem .6rem;
    border-radius: 20px;
    font-weight: 600;
}

.user-badge.active {
    background: #e8f5e9;
    color: #2e7d32;
}

.user-badge.inactive {
    background: #eeeeee;
    color: #666;
}

.user-badge.pending {
    background: #fff8e1;
    color: #f57f17;
}

/* =====================================================
   META
   ===================================================== */

.user-meta {
    font-size: .85rem;
    display: flex;
    flex-direction: column;
    gap: .4rem;
}

.user-meta .label {
    font-weight: 600;
    color: #555;
    display: block;
}

.user-meta .value {
    color: #222;
}

/* =====================================================
   CARD ACTIONS (INDEX)
   ===================================================== */

.user-actions {
    margin-top: auto;
    display: flex;
    gap: .5rem;
}

/* =====================================================
   CARD BUTTONS (INDEX)
   ===================================================== */

.btn-card {
    display: inline-block;
    padding: .4rem .75rem;
    font-size: .85rem;
    font-weight: 500;
    border-radius: 6px;
    text-decoration: none;
}

.btn-card-primary {
    background: #1976d2;
    color: #fff;
}

.btn-card-primary:hover {
    background: #1565c0;
}

.btn-card-secondary {
    background: #f0f0f0;
    color: #333;
}

.btn-card-secondary:hover {
    background: #e0e0e0;
}

/* =====================================================
   USER EDIT
   ===================================================== */

.user-edit-wrapper {
    max-width: 700px;
    margin: 2rem auto;
}

.user-edit-title {
    margin-bottom: 1.5rem;
}

.user-edit-title span {
    font-weight: 400;
    color: #555;
}

/* =====================================================
   FORM
   ===================================================== */

.user-form {
    background: #fff;
    padding: 1.5rem;
    border: 1px solid #ddd;
    border-radius: 6px;
    display: flex;
    flex-direction: column;
    gap: 1.2rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: .4rem;
}

.form-group label {
    font-size: .85rem;
    font-weight: 600;
    color: #444;
}

.user-form input,
.user-form select {
    padding: .5rem .6rem;
    font-size: .9rem;
    border: 1px solid #ccc;
    border-radius: 6px;
}

.form-static {
    padding: .5rem .6rem;
    background: #f7f7f7;
    border-radius: 6px;
    font-size: .9rem;
}

.form-static small {
    display: block;
    margin-top: .25rem;
    color: #777;
}

/* =====================================================
   CHECKBOX
   ===================================================== */

.checkbox {
    display: flex;
    align-items: center;
    gap: .5rem;
}

.checkbox input {
    margin: 0;
}


/* =====================================================
   GLOBAL BUTTONS
   ===================================================== */

.btn-primary,
.btn-secondary {
    display: inline-block;
    padding: .5rem .9rem;
    font-size: .9rem;
    font-weight: 500;
    border-radius: 6px;
    text-decoration: none;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: #1976d2;
    color: #fff;
}

.btn-primary:hover {
    background: #1565c0;
}

.btn-secondary {
    background: #f0f0f0;
    color: #333;
}

.btn-secondary:hover {
    background: #e0e0e0;
}



</style>
CSS;
