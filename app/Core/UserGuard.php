<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Služba pro kontrolu speciálních uživatelských rolí a ochrany systémových účtů.
 * Používá se pro identifikaci privilegovaných uživatelů (root, domain admin)
 * a pro zajištění, že systémové účty nelze upravovat nebo deaktivovat.
 */
final class UserGuard
{
    // TODO: [SECURITY] Přidat konstanty pro názvy speciálních rolí
    // TODO: [MAINTENANCE] Přidat whitelist chráněných rolí do konfigurace

    /**
     * Určuje, zda je uživatel systémový root administrátor.
     * Root má nejvyšší práva v celé aplikaci napříč všemi tenanty.
     *
     * Očekává:
     * - Pole uživatele obsahuje klíč 'global_role'
     *
     * TODO: [SECURITY] Root by neměl mít přístup k běžným tenant datům pro izolaci
     * TODO: [AUDIT] Přidat logování všech akcí provedených root uživatelem
     *
     * @param array<string, mixed> $user Data uživatele z databáze
     * @return bool TRUE pokud je uživatel root administrátor
     */
    public static function isRoot(array $user): bool
    {
        return $user['global_role'] === 'root';
    }

    /**
     * Určuje, zda je uživatel domain admin (správce domény/tenantu).
     * Domain admin má plná práva v rámci svého tenantu, ale neglobálně.
     *
     * Očekává:
     * - Pole uživatele obsahuje klíč 'domain_admin'
     * - Hodnota je 0/1 nebo boolean
     *
     * TODO: [FEATURE] Přidat podporu pro multi-domain admin (více tenantů)
     * TODO: [BUSINESS] Zvážit hierarchii admin -> domain admin -> manager
     *
     * @param array<string, mixed> $user Data uživatele z databáze
     * @return bool TRUE pokud je uživatel domain admin
     */
    public static function isDomainAdmin(array $user): bool
    {
        return (int)($user['domain_admin'] ?? 0) === 1;
    }

    /**
     * Určuje, zda je uživatel chráněný systémový účet.
     * Chráněné účty nelze upravovat, deaktivovat ani mazat běžnými operacemi.
     *
     * TODO: [SECURITY] Přidat separátní ochranu pro root a domain admin (různé úrovně)
     * TODO: [AUDIT] Logovat všechny pokusy o změnu chráněných účtů
     *
     * @param array<string, mixed> $user Data uživatele z databáze
     * @return bool TRUE pokud je uživatel chráněný (root nebo domain admin)
     */
    public static function isProtected(array $user): bool
    {
        return self::isRoot($user) || self::isDomainAdmin($user);
    }
}