<?php
declare(strict_types=1);

namespace App\Services\Guards;

final class BanType
{
    public const BAD_LOGIN        = 'BAD_LOGIN';
    public const SCANNING_WARNING = 'SCANNING_WARNING';
    public const SCANNING_BAN     = 'SCANNING_BAN';
    public const ACCOUNT_BAN      = 'ACCOUNT_BAN';

    /**
     * @return array<string, array{description: string, message: string}>
     */
    public static function definitions(): array
    {
        return [
            self::BAD_LOGIN => [
                'description' => 'Příliš mnoho neúspěšných pokusů o přihlášení.',
                'message' => 'Přihlášení je dočasně zablokováno.',
            ],
            self::SCANNING_WARNING => [
                'description' => 'Podezřelé požadavky na neexistující nebo chráněné cesty.',
                'message' => 'Přístup byl dočasně omezen.',
            ],
            self::SCANNING_BAN => [
                'description' => 'Opakované podezřelé požadavky; zákaz zařízení.',
                'message' => 'Přístup byl dočasně zablokován.',
            ],
            self::ACCOUNT_BAN => [
                'description' => 'Zákaz přístupu ke konkrétnímu uživatelskému účtu.',
                'message' => 'Účet je dočasně zablokován.',
            ],
        ];
    }

    public static function description(string $type): string
    {
        return self::definitions()[$type]['description']
            ?? 'Neznámý typ zákazu.';
    }

    public static function message(string $type): string
    {
        return self::definitions()[$type]['message']
            ?? 'Přístup je dočasně zablokován.';
    }

    public static function isValid(string $type): bool
    {
        return isset(self::definitions()[$type]);
    }
}