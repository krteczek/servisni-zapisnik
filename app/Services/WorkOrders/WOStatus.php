<?php
declare(strict_types=1);

namespace App\Services\WorkOrders;


final class WOStatus
{
    public const NEW            = 'new';
    public const IN_PROGRESS    = 'in_progress';
    public const DONE           = 'done';
    public const CANCELLED      = 'cancelled';
    public const EXPORTED       = 'exported';

    public static function all(): array
    {
        return [
            self::NEW,
            self::IN_PROGRESS,
            self::DONE,
            self::CANCELLED,
            self::EXPORTED,
        ];
    }

    public static function isNew(string $status): bool
    {
        return $status === self::NEW;
    }

    public static function inProgress(string $status): bool
    {
        return $status === self::IN_PROGRESS;
    }

    public static function isDone(string $status): bool
    {
        return $status === self::DONE;
    }

    public static function isCancelled(string $status): bool
    {
        return $status === self::CANCELLED;
    }

    public static function isExported(string $status): bool
    {
        return $status === self::EXPORTED;
    }

    public static function isClosed(string $status): bool
    {
        return self::isDone($status)
            || self::isCancelled($status)
            || self::isExported($status);
    }
        public static function isOpen(string $status): bool
    {
        return self::isNew($status)
            || self::inProgress($status);
    }


}