<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Služba pro správu konfigurace aplikace s podporou dot notation a lazy loading.
 * Načítá konfigurační soubory z adresáře Config a cachuje je pro celý request.
 *
 * Implementuje fail-fast přístup - vyhazuje výjimku při chybějících konfiguračních souborech.
 */
final class Config
{
    /**
     * @var array Cache načtených konfiguračních souborů [filename => data]
     */
    private static array $cache = [];

    /**
     * Získá konfigurační hodnotu pomocí dot notation (např. 'database.host').
     * Soubory se načítají pouze při prvním přístupu a cachují se pro celý request.
     *
     * Očekává:
     * - Konfigurační soubory jsou v adresáři ../Config/
     * - Soubory mají příponu .php a vracejí pole
     * - Klíč obsahuje alespoň jeden segment (file.key)
     *
     * TODO: [PERFORMANCE] Přidat opcode caching (OPcache) pro konfigurační soubory
     * TODO: [FEATURE] Přidat podporu pro prostředí (dev/staging/prod) s dědičností konfigurací
     *
     * @param string $key Klíč v dot notation (např. 'database.connections.mysql.host')
     * @param mixed $default Výchozí hodnota pokud klíč neexistuje
     * @return mixed Konfigurační hodnota nebo výchozí hodnota
     * @throws \RuntimeException Pokud konfigurační soubor neexistuje
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        [$file, $path] = self::parseKey($key);
        //var_dump($key, $path);
        if (!isset(self::$cache[$file])) {
            $configPath = __DIR__ . '/../Config/' . $file . '.php';

            if (!is_file($configPath)) {
                throw new \RuntimeException("Config soubor {$file} neexistuje");
            }

            // TODO: [SECURITY] Zvážit validaci struktury načtené konfigurace
            // TODO: [MAINTENANCE] Přidat logování načtenýchonfigurací v dev prostředí
            
            self::$cache[$file] = require $configPath;

            if (!is_array(self::$cache[$file])) {
                var_dump($file, self::$cache[$file]);
                die('CONFIG ERROR');
            }
        }
var_dump(self::$cache[$file]);

        $value = self::$cache[$file];
foreach ($path as $segment) {
    if (!is_array($value)) {
        throw new \RuntimeException(
            "Config error for key '{$key}' – segment '{$segment}', value type: " . gettype($value)
        );
    }

    if (!array_key_exists($segment, $value)) {
        return $default;
    }

    $value = $value[$segment];
}/*
        foreach ($path as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }*/

        return $value;
    }

    /**
     * Parsuje dot notation klíč na název souboru a cestu v poli.
     * Např. 'database.connections.mysql' → ['database', ['connections', 'mysql']]
     *
     * TODO: [SECURITY] Přidat sanitizaci názvu souboru pro prevenci path traversal
     * TODO: [MAINTENANCE] Zvážit podporu pro složitější cesty (např. s čísly)
     *
     * @param string $key Klíč v dot notation
     * @return array Pole obsahující [název_souboru, pole_cesty]
     */
    private static function parseKey(string $key): array
    {
        $parts = explode('.', $key);
        $file  = array_shift($parts);

        return [$file, $parts];
    }
}