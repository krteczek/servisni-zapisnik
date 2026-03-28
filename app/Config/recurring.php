<?php
declare(strict_types=1);
//nastavení defaultní hodnoty pro recurring
return [

    'frequencies' => [
        'daily'   => 'Denně',
        'weekly'  => 'Týdně',
        'monthly' => 'Měsíčně',
        'yearly'  => 'Ročně',
    ],

    'default' => [
        'frequency_type'      => 'monthly',
        'frequency_value'     => 1,
        'warning_days_before' => 1,
        'next_due_date'       => date('Y-m-d', strtotime('+1 day')),//vždy zítra
    ],
    'limits' => [
        'min_frequency_value' => 1,
        'max_frequency_value' => 365,
        'min_warning_days' => 0,
        'max_warning_days' => 15,


    ],

    'runner' => [
        'batch_size_companies' => 20,
        'batch_size'           => 20,
        'lock_seconds'         => 30,

    ],
];