-- admin všechno
INSERT INTO role_permissions
SELECT r.id, p.id FROM roles r, permissions p WHERE r.code = 'admin';

-- mistr
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.code IN ('dashboard.view','users.view','tasks.assign')
WHERE r.code = 'mistr';
