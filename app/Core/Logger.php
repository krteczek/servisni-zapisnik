<?php
declare(strict_types=1);

namespace App\Core;

//use Psr\Log\LogLevel;

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
    
    private static ?LoggerInterface $instance = null;

    // TODO: [PERFORMANCE] Přidat bufferování záznamů pro batch zápis
    // TODO: [SECURITY] Validovat, že logFile je v povoleném adresáři

    /**
     * Vytvoří novou instanci loggeru s určeným log souborem.
     *
     * @param string $logFile Absolutní nebo relativní cesta k log souboru
     * @throws \RuntimeException Pokud adresář log souboru není zapisovatelný
     */
    public function __construct(string $logFile)
    {
        $this->logFile = $logFile;
        
        // TODO: [RELIABILITY] Zkontrolovat zapisovatelnost adresáře při vytvoření
        $dir = dirname($logFile);
        if (!is_writable($dir)) {
            throw new \RuntimeException("Log directory is not writable: {$dir}");
        }
    }



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
     * @param array $context Kontextová data
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
     * @param array $context Kontextová data
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
     * @param array $context Kontextová data
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
     * @param array $context Kontextová data
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
     * @param array $context Kontextová data
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
     * @param array $context Kontextová data
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
     * @param array $context Kontextová data
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
     * @param array $context Kontextová data
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
     * - Používá file_put_contents s FILE_APPEND (možný race condition)
     *
     * TODO: [PERFORMANCE] Použít flock() pro prevenci race condition při paralelních zápisech
     * TODO: [RELIABILITY] Implementovat retry mechanismus při selhání zápisu
     *
     * @param string $level Úroveň logu
     * @param string $message Text zprávy
     * @param array $context Kontextová data
     * @return void
     */
    public function log(string $level, string $message, array $context = []): void
    {
        $date = date('Y-m-d H:i:s');
        $msg  = $this->interpolate($message, $context);

        $line = "[{$date}] {$level}: {$msg}\n";

        // TODO: [SECURITY] Omezit velikost log souboru a implementovat rotaci
        file_put_contents($this->logFile, $line, FILE_APPEND);
    }

    /**
     * Nahradí placeholdery {key} v message hodnotami z context.
     * Podporuje pouze skalární hodnoty (string, int, float, bool).
     *
     * TODO: [FEATURE] Přidat podporu pro objekty (__toString) a pole (json_encode)
     * TODO: [SECURITY] Escapovat speciální znaky pro prevenci injection do logů
     *
     * @param string $message Zpráva s placeholdery
     * @param array $context Kontextová data
     * @return string Interpolovaná zpráva
     */
    private function interpolate(string $message, array $context): string
    {
        foreach ($context as $key => $value) {
            if (is_scalar($value)) {
                $message = str_replace('{' . $key . '}', (string) $value, $message);
            }
        }

        return $message;
    }
}