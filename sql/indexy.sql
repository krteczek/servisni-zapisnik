CREATE INDEX idx_tm_current
    ON team_memberships (user_id, valid_to);

CREATE INDEX idx_tasks_team_status
    ON tasks (team_id, status);

CREATE INDEX idx_tasks_recurring
    ON tasks (recurring_task_id);

CREATE INDEX idx_task_assignments_task
    ON task_assignments (task_id);

CREATE INDEX idx_work_orders_status
    ON work_orders (status);
