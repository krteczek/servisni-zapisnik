<?php
declare(strict_types=1);

use App\Controllers\AuthController;

return [
    [
        'method'  => 'GET',
        'path'    => '/',
        'handler' => [AuthController::class, 'loginForm'],
    ],
    [
        'method'  => 'POST',
        'path'    => '/login',
        'handler' => [AuthController::class, 'login'],
    ],
];