CREATE TABLE work_orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    external_number VARCHAR(100) NULL UNIQUE,

    title VARCHAR(255) NOT NULL,
    description TEXT NULL,

    source ENUM('email','phone','personal','system') NOT NULL,
    requested_by VARCHAR(255) NULL,

    priority ENUM('low','normal','high','emergency') NOT NULL DEFAULT 'normal',
    status ENUM('new','in_progress','done','exported','cancelled') NOT NULL DEFAULT 'new',

    created_by_user_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    closed_at DATETIME NULL,

    CONSTRAINT fk_wo_user
        FOREIGN KEY (created_by_user_id) REFERENCES users(id)
) ENGINE=InnoDB;