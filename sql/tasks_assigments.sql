CREATE TABLE task_assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    task_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,

    minutes_spent INT UNSIGNED NOT NULL,
    note TEXT NULL,

    created_by_user_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_ta_task
        FOREIGN KEY (task_id) REFERENCES tasks(id),

    CONSTRAINT fk_ta_user
        FOREIGN KEY (user_id) REFERENCES users(id),

    CONSTRAINT fk_ta_creator
        FOREIGN KEY (created_by_user_id) REFERENCES users(id)
) ENGINE=InnoDB;