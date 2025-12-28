CREATE TABLE tasks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    team_id INT UNSIGNED NOT NULL,
    work_order_id INT UNSIGNED NULL,
    recurring_task_id INT UNSIGNED NULL,

    title VARCHAR(255) NOT NULL,
    description TEXT NULL,

    status ENUM('open','done','cancelled') NOT NULL DEFAULT 'open',

    created_by_user_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    done_at DATETIME NULL,

    CONSTRAINT fk_task_team
        FOREIGN KEY (team_id) REFERENCES teams(id),

    CONSTRAINT fk_task_work_order
        FOREIGN KEY (work_order_id) REFERENCES work_orders(id),

    CONSTRAINT fk_task_recurring
        FOREIGN KEY (recurring_task_id) REFERENCES recurring_tasks(id),

    CONSTRAINT fk_task_creator
        FOREIGN KEY (created_by_user_id) REFERENCES users(id)
) ENGINE=InnoDB;