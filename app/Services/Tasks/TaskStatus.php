<?php
declare(strict_types=1);

namespace App\Services\Tasks;


final class TaskStatus
{
    public const OPEN      = 'open';
    public const DONE      = 'done';
    public const CANCELLED = 'cancelled';

    /**
     * @return array<int, self::OPEN|self::DONE|self::CANCELLED>
     */
    public static function all(): array
    {
        return [
            self::OPEN,
            self::DONE,
            self::CANCELLED,
        ];
    }

    public static function isOpen(string $status): bool
    {
        return $status === self::OPEN;
    }

    public static function isDone(string $status): bool
    {
        return $status === self::DONE;
    }

    public static function isCancelled(string $status): bool
    {
        return $status === self::CANCELLED;
    }

    public static function isClosed(string $status): bool
    {
        return self::isDone($status)
            || self::isCancelled($status);
    }
}