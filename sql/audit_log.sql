CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity VARCHAR(50) NOT NULL,
    entity_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    changes JSON NULL,
    user_id INT NULL,
    created_at DATETIME NOT NULL
);
