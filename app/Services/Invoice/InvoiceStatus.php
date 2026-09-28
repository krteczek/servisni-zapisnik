<?php
declare(strict_types=1);

namespace App\Services\Invoice;

final class InvoiceStatus
{
    public const DRAFT     = 'draft';
    public const ISSUED    = 'issued';
    public const PAID      = 'paid';
    public const CANCELLED = 'cancelled';

    /**
     * @return array<int, self::DRAFT|self::ISSUED|self::PAID|self::CANCELLED>
     */
    public static function all(): array
    {
        return [
            self::DRAFT,
            self::ISSUED,
            self::PAID,
            self::CANCELLED,
        ];
    }

    public static function isDraft(string $status): bool
    {
        return $status === self::DRAFT;
    }

    public static function isIssued(string $status): bool
    {
        return $status === self::ISSUED;
    }

    public static function isPaid(string $status): bool
    {
        return $status === self::PAID;
    }

    public static function isCancelled(string $status): bool
    {
        return $status === self::CANCELLED;
    }

    public static function isFinal(string $status): bool
    {
        return self::isPaid($status)
            || self::isCancelled($status);
    }

    public static function isEditable(string $status): bool
    {
        return self::isDraft($status);
    }
}