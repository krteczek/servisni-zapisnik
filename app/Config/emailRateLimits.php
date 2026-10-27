<?php
declare(strict_types=1);
/**********************************************************************
 *    emailRateLimits.php
 *  ---------------------------------------------
 *  nastavení počtu povolených pokusů v čase a doba blokace přístupu  *
 **********************************************************************/
use App\Services\Tokens\TokenType;

/** možná by stálo za to, aby tohle mohl ovlivnovat root ve svém rozhraní... */
return [

    TokenType::PASSWORD_RESET => [
        'time' => 15,     // minut
        'rate' => 15,      // pokusů
        'ban'  => 60      // minut blokace
    ],

    TokenType::COMPANY_CREATE => [
        'time' => 60,
        'rate' => 3,
        'ban'  => 180
    ],

    TokenType::INVITATION => [
        'time' => 60,
        'rate' => 10,
        'ban'  => 180
    ],
];