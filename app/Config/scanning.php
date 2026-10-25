<?php
declare(strict_types=1);

return [
    'paths' => [
        'wp_search' => [
            'contains' => [
                'wp-login.php',
                'wp-admin',
                'wp-content',
                'wp-includes',
                'xmlrpc.php',
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

    'ban_type' => 'SCANNING_BAN',
];