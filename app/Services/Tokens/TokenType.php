<?php
declare(strict_types=1);

namespace App\Services\Tokens;

final class TokenType
{
    public const COMPANY_CREATE   = 'company_create';
    public const INVITATION       = 'invitation';
    public const PASSWORD_RESET   = 'password_reset';
    public const ACTIVATE_USER    = 'activate_user';

    public static function all(): array
    {
        return [
            self::COMPANY_CREATE,
            self::INVITATION,
            self::PASSWORD_RESET,
            self::ACTIVATE_USER,
        ];
    }
}