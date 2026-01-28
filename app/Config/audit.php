<?php
declare(strict_types=1);

return [
    'auditables' => [
        'users',
        'companies',
        'roles',
        'permissions',

        // work část
        'work_orders',
        'tasks',
    ],

    'ignores' => [
        'task_assignments',
        'work_order_entries',
        'audit_logs',
    ],
];
