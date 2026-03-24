<?php
declare(strict_types=1);

return [
    'enabled' => true,

    'auditables' => [
        'users',
        'companies',
        'roles',
        'permissions',
        'work_orders',
        'tasks',
        'teams',
    ],

    'ignores' => [
        'task_assignments',
        'work_order_entries',
        'audit_logs',
    ],
];

