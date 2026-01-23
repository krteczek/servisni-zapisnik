ALTER TABLE task_assignments
ADD parent_id INT UNSIGNED NULL
AFTER id;

CREATE INDEX idx_task_assignments_parent
ON task_assignments (parent_id);


ALTER TABLE recurring_tasks
ADD parent_id INT UNSIGNED NULL
AFTER id;

CREATE INDEX idx_recurring_tasks_parent
ON recurring_tasks (parent_id);




ALTER TABLE tasks
ADD parent_id INT UNSIGNED NULL
AFTER id;

CREATE INDEX idx_tasks_parent
ON tasks (parent_id);


ALTER TABLE work_orders
ADD estimated_hours DECIMAL(8,2) NULL
AFTER priority;