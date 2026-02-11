<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Jednoduchý PSR-4 kompatibilní autoloader pro aplikaci.
 * Automaticky načtě třídy z adresářové struktury odpovídající namespace.
 * 
 * Tato implementace je určena pro vývojové prostředí a malé projekty.
 * Ve větších aplikacích zvažte použití Composer autoloaderu s optimalizovaným classmap.
 */
class Autoloader
{
    /**
     * Registruje autoloader v systému.
     * Autoloader je zaregistrován pouze pro namespace začínající 'App\\'.
     *
     * Vedlejší efekty:
     * - Mění chování PHP při hledání tříd
     * - Zahrnuje soubory pomocí `require`
     *
     * TODO: [PERFORMANCE] V produkčním nasazení použít Composer classmap pro rychlejší načítání
     * TODO: [MAINTENANCE] Přidat logování pro debugging chybějících tříd v dev prostředí
     *
     * @return void
     */
    public static function register(): void
    {
        spl_autoload_register(function (string $class) {
            $prefix = 'App\\';
            $baseDir = __DIR__ . '/../';

            // TODO: [SECURITY] Zvážit whitelist povolených namespaces pro omezení include path traversal
            if (!str_starts_with($class, $prefix)) {
                return; // Nezpracováváme třídy mimo App namespace
            }

            $relativeClass = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            // TODO: [PERFORMANCE] Přidat cache cest k souborům pro opakované volání
            if (file_exists($file)) {
                // TODO: [SECURITY] V produkci zvážit require_once pro prevenci opakovaného načtení
                require $file;
            } else {
                // TODO: [DEBUG] V dev prostředí logovat varování o chybějící třídě
                // throw new \Exception("Class {$class} not found at {$file}");
            }
        });
    }
}