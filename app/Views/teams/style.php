<?php

$css = <<<CSS
<style>
/* ===== Layout ===== */

.create-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 32px;
}

/* ===== Alert spacing ===== */

.alert-top {
    max-width: 1200px;
    margin: 0 auto 20px;
}

.alert-spacing {
    margin-bottom: 24px;
}

/* ===== Divider ===== */

.divider {
    margin: 32px 0;
    border: 0;
    border-top: 1px solid #e5e7eb;
}

/* ===== Section headings ===== */

.section-title {
    margin-top: 0;
    margin-bottom: 8px;
}

.section-subtext {
    color: #4b5563;
    margin-bottom: 24px;
}

.subsection-title {
    margin-top: 0;
    margin-bottom: 16px;
    font-size: 1.1rem;
}

/* ===== Color picker ===== */

.color-row {
    display: flex;
    gap: 10px;
    align-items: center;
}

.color-input {
    width: 60px;
    height: 40px;
    padding: 2px;
    border-radius: 6px;
    border: 1px solid #cfd6de;
    background: transparent;
    cursor: pointer;
}

.color-hint {
    color: #4b5563;
    font-size: 0.9rem;
}

/* ===== Dual list layout ===== */

.dual-list {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

/* ===== Member rows ===== */

.member-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    padding: 8px;
    background: #f9fafb;
    border-radius: 6px;
}

.member-name {
    flex: 1;
    font-weight: 500;
}

.member-form {
    margin: 0;
}

.small-btn {
    padding: 4px 10px;
}

.arrow {
    font-size: 1.2rem;
}

/* ===== Empty state ===== */

.empty-box {
    padding: 20px;
    background: #f9fafb;
    border-radius: 8px;
    text-align: center;
    color: #6b7280;
}

/* ===== Footer section ===== */

.section-footer {
    margin-top: 32px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

/* ===== Help card tweaks ===== */

.help-list {
    padding-left: 1.2rem;
}

.help-summary {
    cursor: pointer;
    font-weight: 600;
}

.help-details-text {
    margin-top: 12px;
}

</style>

CSS;
