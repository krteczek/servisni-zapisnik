<?php
declare(strict_types=1);

namespace App\Core;

//use Psr\Log\LoggerInterface;

/**
 * Service locator/holder pro PSR-3 kompatibilní logger.
 * Poskytuje centrální přístup k loggeru v celé aplikaci s fallback na NullLogger.
 *
 * Implementuje pattern "service holder" pro dependency injection bez nutnosti
 * předávat logger všem třídám konstruktorem.
 */
final class LoggerHolder
{
    /**
     * @var LoggerInterface|null Globální instance loggeru
     */
    private static ?LoggerInterface $logger = null;

    // TODO: [MAINTENANCE] Přidat podporu pro více loggerů (např. pro různé kanály)
    // TODO: [PERFORMANCE] Zvážit lazy initialization loggeru až při prvním použití

    /**
     * Nastaví globální logger instance.
     * Mělo by být voláno při bootstrapu aplikace (např. index.php).
     *
     * Vedlejší efekty:
     * - Mění statický stav třídy
     * - Ovlivňuje všechny následné volání get()
     *
     * TODO: [CONFIG] Přidat validaci, že logger implementuje PSR-3 LoggerInterface
     * TODO: [SECURITY] Zvážit immutabilitu po nastavení (prevent re-set)
     *
     * @param LoggerInterface $logger Instance loggeru (např. Monolog)
     * @return void
     */
    public static function set(LoggerInterface $logger): void
    {
        self::$logger = $logger;
    }

    /**
     * Vrátí globální logger nebo NullLogger jako fallback.
     * NullLogger zajišťuje, že aplikace nikdy nezpůsobí chybu kvůli chybějícímu loggeru.
     *
     * TODO: [MAINTENANCE] Přidat logování warningu při použití NullLogger v dev prostředí
     * TODO: [FEATURE] Přidat možnost získat logger pro konkrétní kanál/channel
     *
     * @return LoggerInterface Instance loggeru (nikdy nevrátí null)
     */
    public static function get(): LoggerInterface
    {
        if (self::$logger === null) {
            // TODO: [MAINTENANCE] Přesunout NullLogger do samostatné třídy
            // fallback – NullLogger (no-op)
            return new class implements LoggerInterface {
                public function emergency(string $message, array $context = []): void {}
                public function alert(string $message, array $context = []): void {}
                public function critical(string $message, array $context = []): void {}
                public function error(string $message, array $context = []): void {}
                public function warning(string $message, array $context = []): void {}
                public function notice(string $message, array $context = []): void {}
                public function info(string $message, array $context = []): void {}
                public function debug(string $message, array $context = []): void {}
                public function log(string $level, string $message, array $context = []): void {}
            };
        }

        return self::$logger;
    }
}