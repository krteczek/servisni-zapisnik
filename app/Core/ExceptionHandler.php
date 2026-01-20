<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;
use App\Core\LoggerHolder;

final class ExceptionHandler
{
    public static function register(): void
    {
        set_exception_handler([self::class, 'handle']);
    }

    public static function handle(Throwable $e): void
    {
LoggerHolder::get()->error(
    $e->getMessage(),
    [
        'exception' => get_class($e),
        'file'      => $e->getFile(),
        'line'      => $e->getLine(),
        'trace'     => $e->getTraceAsString(),
    ]
);

        // 2️⃣ HTTP status
        http_response_code(500);

        // 3️⃣ výstup uživateli
        if (self::isDev()) {
            self::renderDev($e);
        } else {
            self::renderProd();
        }

        exit;
    }

    private static function isDev(): bool
    {
        return ($_ENV['APP_ENV'] ?? 'prod') === 'dev';
    }

    private static function renderProd(): void
    {
        echo 'Došlo k chybě aplikace. Omlouváme se.';
    }

    private static function renderDev(Throwable $e): void
    {
        echo '<h1>Application error</h1>';
        echo '<pre>';
        echo get_class($e) . "\n";
        echo $e->getMessage() . "\n\n";
        echo $e->getFile() . ':' . $e->getLine() . "\n\n";
        echo $e->getTraceAsString();
        echo '</pre>';
    }
}
