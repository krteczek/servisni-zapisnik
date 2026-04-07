<?php
declare(strict_types=1);

return [
   'allowed' => [
        '{PREFIX}',
        '{YEAR}',
        '{MONTH}',
        '{DAY}',
        '{NUMBER}'
        ],
   'default' => [
        'format' => '{PREFIX}-{NUMBER}',// jak má vypadat číslo zakázky: WO-00025
        'prefix' => 'WO',//defaultní prefix
        'number_length' => 5,//počet číslic. Tady: 00001 až 99999
        ],

];
