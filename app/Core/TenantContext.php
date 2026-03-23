<?php
declare(strict_types=1);

namespace App\Core;

use \RuntimeException;
final class TenantContext
{
    private static ?int $companyId = null;

    public static function set(int $companyId): void
    {
       if ($companyId <= 0) {
          throw new RuntimeException('Invalid companyId');
       }
       self::$companyId = $companyId;
    }

    public static function get(): ?int
    {
        return self::$companyId;
    }

    public static function clear(): void
    {
        self::$companyId = null;
    }


}