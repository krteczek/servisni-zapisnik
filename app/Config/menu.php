<?php
declare(strict_types=1);

return [
    [
        'label' => 'Dashboard',
        'url'   => './dashboard',
        'roles' => ['admin', 'mistr', 'predak', 'monter'],
    ],
    [
        'label' => 'Úkoly',
        'url'   => './tasks',
        'roles' => ['mistr', 'predak', 'monter'],
    ],
    [
        'label' => 'Periodické úkoly',
        'url'   => './recurring-tasks',
        'roles' => ['mistr', 'predak'],
    ],
    [
        'label' => 'Lidé',
        'url'   => './users',
        'roles' => ['admin', 'mistr'],
    ],
    [
        'label' => 'Admin',
        'url'   => './admin',
        'roles' => ['admin'],
    ],
    [
    'label' => 'Odhlásit',
    'url'   => './logout',
    'auth'  => true,
],
];