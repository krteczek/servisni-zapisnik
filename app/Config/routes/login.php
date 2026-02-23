<?php
declare(strict_types=1);


[
    'method' => 'GET',
    'path'   => '/login',
    'action' => [AuthController::class, 'loginForm'],
    'auth'   => false,
    'title'  => 'Bó: Přihlášení',
],
[
    'method' => 'POST',
    'path'   => '/login',
    'action' => [AuthController::class, 'login'],
    'auth'   => false,
    'title'  => 'Bó: Přihlášení',
],
