# změna typu sloupce minutes_spent na INT, aby bylo možné 
//ukládat záporné hodnoty
ALTER TABLE task_assignments
MODIFY minutes_spent INT NOT NULL;

# odstranění sloupce is_recurring_master
ALTER TABLE tasks
DROP COLUMN is_recurring_master;


