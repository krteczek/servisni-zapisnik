<?php
// app/Config/roles.php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\UserController;

return [
    'default' => 'monter',

    'roles' => [
        'admin'  => 'Admin',
        'mistr'  => 'Mistr',
        'predak' => 'Předák',
        'monter' => 'Montér',
    ],
];