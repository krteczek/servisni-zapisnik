<?php
declare(strict_types=1);

namespace App\Core;

use DateTime;
use RuntimeException;
use Stringable;
use Throwable;

/**
 * Jednoduchá implementace PSR-3 loggeru, který zapisuje do souboru.
 * Podporuje všechny úrovně logování a interpolaci kontextových proměnných.
 *
 * Tato implementace je vhodná pro menší aplikace nebo jako fallback.
 * Pro produkční nasazení zvažte použití Monologu s více handlery.
 */
final class Logger implements LoggerInterface
{
    /**
     * @var string Cesta k log souboru
     */
    private string $logFile;

    /**
     * @var LoggerInterface|null Singleton instance loggeru
     */
    private static ?LoggerInterface $instance = null;

    // TODO: [PERFORMANCE] Přidat bufferování záznamů pro batch zápis
    // TODO: [SECURITY] Validovat, že logFile je v povoleném adresáři

    /**
     * Vytvoří novou instanci loggeru s určeným log souborem.
     *
     * @param string $logFile Absolutní nebo relativní cesta k log souboru
     * @throws RuntimeException Pokud adresář log souboru není zapisovatelný
     */
    public function __construct(string $logFile)
    {
        $this->logFile = $logFile;

        // TODO: [RELIABILITY] Zkontrolovat zapisovatelnost adresáře při vytvoření
        $dir = dirname($logFile);
        if (!is_writable($dir)) {
            throw new RuntimeException("Log directory is not writable: {$dir}");
        }
    }

    /**
     * Vrátí singleton instanci loggeru.
     *
     * Pokud instance ještě neexistuje, vytvoří ji s cestou z konfigurace
     * (nebo s výchozí cestou do storage/logs/app.log).
     *
     * @return LoggerInterface
     */
    public static function instance(): LoggerInterface
    {
        if (self::$instance === null) {
            $path = Config::get('app.log_file')
                ?? __DIR__ . '/../../storage/logs/app.log';

            self::$instance = new self($path);
        }

        return self::$instance;
    }

    /**
     * Systém je nepoužitelný.
     *
     * @param string $message Text zprávy
     * @param array<string, mixed> $context Kontextová data
     * @return void
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Je třeba okamžité akce.
     *
     * @param string $message Text zprávy
     * @param array<string, mixed> $context Kontextová data
     * @return void
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Kritická situace.
     *
     * @param string $message Text zprávy
     * @param array<string, mixed> $context Kontextová data
     * @return void
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Chyba runtime.
     *
     * @param string $message Text zprávy
     * @param array<string, mixed> $context Kontextová data
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Výjimečné události, které nejsou chybami.
     *
     * @param string $message Text zprávy
     * @param array<string, mixed> $context Kontextová data
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normální, ale významné události.
     *
     * @param string $message Text zprávy
     * @param array<string, mixed> $context Kontextová data
     * @return void
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Zajímavé události.
     *
     * @param string $message Text zprávy
     * @param array<string, mixed> $context Kontextová data
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Podrobné informace pro debugging.
     *
     * @param string $message Text zprávy
     * @param array<string, mixed> $context Kontextová data
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Logování na libovolné úrovni.
     * Zapíše řádek do log souboru s timestampem a úrovní logu.
     *
     * Vedlejší efekty:
     * - Zapíše data do souboru na disku
     * - Používá file_put_contents s LOCK_EX pro bezpečný zápis
     *
     * TODO: [RELIABILITY] Implementovat retry mechanismus při selhání zápisu
     *
     * @param string $level Úroveň logu
     * @param string $message Text zprávy
     * @param array<string, mixed> $context Kontextová data
     * @return void
     */
    public function log(string $level, string $message, array $context = []): void
    {
        $date = (new DateTime())->format('Y-m-d H:i:s.u');

        $msg = $this->interpolate($message, $context);

        $line = sprintf(
            "[%s] %-7s %s",
            $date,
            strtoupper($level) . ':',
            $msg
        );

        // Context se připojí vždy, když není prázdný
        if ($context !== []) {
            $encoded = json_encode(
                $context,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            if ($encoded !== false) {
                $line .= PHP_EOL . $encoded;
            }
        }

        $line .= "\n";

        file_put_contents($this->logFile, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Nahradí placeholdery {key} v message hodnotami z context.
     *
     * Podporuje:
     * - skalární hodnoty (string, int, float, bool) a null
     * - Throwable (použije se pouze getMessage(), ne celý trace)
     * - objekty implementující Stringable (převedou se na string)
     * - vše ostatní (json_encode fallback)
     *
     * Pořadí podmínek je důležité:
     * 1. Throwable musí být první — jinak by ho zachytil Stringable check
     *    a do logu by se dostal celý stack trace místo jen zprávy.
     * 2. Skaláry a null — přímý převod na string.
     * 3. Stringable objekty — převod na string.
     * 4. Vše ostatní — json_encode fallback.
     *
     * TODO: [FEATURE] Přidat podporu pro objekty bez __toString (reflection?)
     * TODO: [SECURITY] Escapovat speciální znaky pro prevenci injection do logů
     *
     * @param string $message Zpráva s placeholdery
     * @param array<string, mixed> $context Kontextová data
     * @return string Interpolovaná zpráva
     */
    private function interpolate(string $message, array $context): string
    {
        foreach ($context as $key => $value) {
            if ($value instanceof Throwable) {
                // Throwable má přednost — chceme jen getMessage(), ne celý trace
                $replace = $value->getMessage();
            } elseif (is_scalar($value) || $value === null) {
                $replace = (string) $value;
            } elseif ($value instanceof Stringable) {
                $replace = (string) $value;
            } else {
                $encoded = json_encode(
                    $value,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );

                $replace = $encoded !== false ? $encoded : '[unencodable]';
            }

            $message = str_replace('{' . $key . '}', $replace, $message);
        }

        return $message;
    }
}