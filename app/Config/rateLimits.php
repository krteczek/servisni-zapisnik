<?php
declare(strict_types=1);
/**********************************************************************
 *  nastavení počtu povolených pokusů v čase a doba blokace přístupu  *
 **********************************************************************/


return [

    'PASSWORD_RESET' => [
        'time' => 15,     // minut
        'rate' => 5,      // pokusů
        'ban'  => 60      // minut blokace
    ],

    'COMPANY_CREATE' => [
        'time' => 60,
        'rate' => 3,
        'ban'  => 180
    ],

    'INVITATION' => [
        'time' => 60,
        'rate' => 3,
        'ban'  => 180
    ],

];