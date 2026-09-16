<?php
declare(strict_types=1);

namespace App\Core;

/**
 * PSR-3 kompatibilní rozhraní pro logger.
 * Definuje standardní metody pro logování na různých úrovních závažnosti.
 *
 * Toto rozhraní umožňuje snadnou výměnu logger implementací (Monolog, custom, atd.)
 * a zajišťuje konzistentní logování napříč celou aplikací.
 *
 * @link https://www.php-fig.org/psr/psr-3/ PSR-3 Specification
 */
interface LoggerInterface
{
    /**
     * Systém je nepoužitelný.
     *
     * @param string $message Text zprávy
     * @param array<string, mixed> $context Kontextová data (např. ['user_id' => 123, 'ip' => '192.168.1.1'])
     * @return void
     */
    public function emergency(string $message, array $context = []): void;

    /**
     * Je třeba okamžité akce.
     * Např. celý web je offline, databáze nedostupná, atd.
     *
     * @param string $message Text zprávy
     * @param array<string, mixed> $context Kontextová data
     * @return void
     */
    public function alert(string $message, array $context = []): void;

    /**
     * Kritická situace.
     * Např. neočekávaná výjimka, neošetřená chyba aplikace.
     *
     * @param string $message Text zprávy
     * @param array<string, mixed> $context Kontextová data
     * @return void
     */
    public function critical(string $message, array $context = []): void;

    /**
     * Chyba runtime, která nevyžaduje okamžitou akci,
     * ale měla by být zaznamenána a monitorována.
     *
     * @param string $message Text zprávy
     * @param array<string, mixed> $context Kontextová data
     * @return void
     */
    public function error(string $message, array $context = []): void;

    /**
     * Výjimečné události, které nejsou chybami.
     * Např. použití zastaralé API, neoptimální použití, atd.
     *
     * @param string $message Text zprávy
     * @param array<string, mixed> $context Kontextová data
     * @return void
     */
    public function warning(string $message, array $context = []): void;

    /**
     * Normální, ale významné události.
     *
     * @param string $message Text zprávy
     * @param array<string, mixed> $context Kontextová data
     * @return void
     */
    public function notice(string $message, array $context = []): void;

    /**
     * Zajímavé události.
     * Např. uživatel se přihlásil, SQL logy.
     *
     * @param string $message Text zprávy
     * @param array<string, mixed> $context Kontextová data
     * @return void
     */
    public function info(string $message, array $context = []): void;

    /**
     * Podrobné informace pro debugging.
     *
     * @param string $message Text zprávy
     * @param array<string, mixed> $context Kontextová data
     * @return void
     */
    public function debug(string $message, array $context = []): void;

    /**
     * Logování na libovolné úrovni.
     *
     * @param string $level Úroveň logu (emergency, alert, critical, error, warning, notice, info, debug)
     * @param string $message Text zprávy
     * @param array<string, mixed> $context Kontextová data
     * @return void
     * @throws \InvalidArgumentException
     */
    public function log(string $level, string $message, array $context = []): void;
}