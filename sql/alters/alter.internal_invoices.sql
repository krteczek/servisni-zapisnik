ALTER TABLE internal_invoices
    ADD COLUMN issued_by BIGINT(20) UNSIGNED DEFAULT NULL
        AFTER created_by,
    ADD COLUMN issued_on DATETIME DEFAULT NULL
        AFTER issued_by;