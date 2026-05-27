<?php
declare(strict_types=1);

namespace App\Services\Settings;

final class BillingMode
{
    public const INTERNAL = 'internal';

    public const EXTERNAL_ACCOUNTANT = 'external_accountant';

    /** @return array<string> */
    public static function all(): array
    {
        return [
            self::INTERNAL,
            self::EXTERNAL_ACCOUNTANT,
        ];
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::all(), true);
    }
}