<?php
declare(strict_types=1);

use \App\Services\Tokens\TokenType;
/** Defaultní nastavení za jak dlouho expirují jednotlivé typy tokenů */

return [
    TokenType::COMPANY_CREATE => 15,
    TokenType::INVITATION => 60 * 24 * 5, // 5 dní
    TokenType::PASSWORD_RESET => 5,
    TokenType::ACTIVATE_USER => 15,
];