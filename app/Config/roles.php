<?php
// app/Config/roles.php

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\UserController;

/** Role jednotlivých uživatelů. root nejde přidat v rámci rozhraní systému */
return [
    'default' => 'monter',

    'roles' => [
        // role pro přístup k administraci firem
        'root'   => 'Root',

        // firemní role
        'admin'  => 'Admin',
        'mistr'  => 'Mistr',
        'predak' => 'Předák',
        'monter' => 'Montér',
    ],
];
