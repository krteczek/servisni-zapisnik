<?php
declare(strict_types=1);

namespace App\Services\Tasks;

final class TaskType
{
    public const NORMAL             = 'normal';
    public const RECURRING_INSTANCE = 'recurring_instance';
    public const RECURRING_MASTER   = 'recurring_master';


    /** @return array{string} **/

    public static function all(): array
    {
        return [
            self::NORMAL,
            self::RECURRING_INSTANCE,
            self::RECURRING_MASTER,
        ];
    }

    public static function isRecurring(string $type): bool
    {
        return self::isMaster($type) || self::isInstance($type);
    }

    public static function isMaster(string $type): bool
    {
        return $type === self::RECURRING_MASTER;
    }

    public static function isInstance(string $type): bool
    {
        return $type === self::RECURRING_INSTANCE;
    }

    public static function isNormal(string $type): bool
    {
        return $type === self::NORMAL;
    }

    public static function isEditable(string $type): bool
    {
        return match ($type) {
            self::NORMAL             => true,
            self::RECURRING_MASTER   => true,
            self::RECURRING_INSTANCE => false,
            default                  => false,
        };
    }
}