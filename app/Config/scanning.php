<?php
declare(strict_types=1);

use App\Services\Guards\BanType;
return [
    'paths' => [
        'wp_search' => [
            'contains' => [
                'wp-login.php',
                'wp-admin',
                'wp-admin.php',
                'wp-content',
                'wp-includes',
                'xmlrpc.php',
                'wp-login',
                'wp',
            ],
        ],

        'other_webpages_scanning' => [
            'contains' => [
                '.env',
                'phpmyadmin',
                'cgi-bin',
            ],
        ],
    ],

    'ban_type' => BanType::SCANNING_BAN,
];