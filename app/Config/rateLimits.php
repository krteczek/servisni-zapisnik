<?php
declare(strict_types=1);
/**********************************************************************
 *  nastavení počtu povolených pokusů v čase a doba blokace přístupu  *
 **********************************************************************/
use App\Services\Tokens\TokenType;

return [

    TokenType::PASSWORD_RESET => [
        'time' => 15,     // minut
        'rate' => 5,      // pokusů
        'ban'  => 60      // minut blokace
    ],

    TokenType::COMPANY_CREATE => [
        'time' => 60,
        'rate' => 3,
        'ban'  => 180
    ],

    TokenType::INVITATION => [
        'time' => 60,
        'rate' => 3,
        'ban'  => 180
    ],

    'BAD_LOGIN' => [
        'time' => 15,
        'rate' => 5,
        'ban'  => 60
    ],


	'SCANNING_WARNING' => [
	    'time' => 15,
	    'rate' => 5,
	    'ban'  => 0
	],

	'SCANNING_BAN' => [
	    'time' => 60,
	    'rate' => 20,
	    'ban'  => 180
	],
];