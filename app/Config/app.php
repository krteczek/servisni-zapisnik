<?php
declare(strict_types=1);

return [
    'env' => file_exists(dirname(__DIR__, 2) . '/.dev') ? 'dev' : 'prod',
    'debug' => file_exists(dirname(__DIR__, 2) . '/.dev'),

    'base_path' => '/servisni-zapisnik/public',

    'error_reporting' => E_ALL,
    'display_errors' => true,
];