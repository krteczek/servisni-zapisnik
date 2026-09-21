<?php
declare(strict_types=1);
/** které tabulky budou podrobovány auditu */
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

