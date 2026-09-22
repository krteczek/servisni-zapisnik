<?php

declare(strict_types=1);

// config/defaultCompanySettings.php

return [
    'billing' => [
        'billing_mode' => 'internal',
        'invoice_due_days' => 14,
        'invoice_number_start' => 1,
        'invoice_number_format' => '{R5}',

        '_confirmation_required' => [
            'invoice_number_start' => false,
            'invoice_number_format' => false,
        ],
    ],

    'work_order_numbering' => [
        'format' => '{PREFIX}-{NUMBER}',
        'prefix' => 'WO',
        'number_length' => 5,
        'next_number' => 1,

        '_confirmation_required' => [],
    ],
];