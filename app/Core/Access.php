<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Služba pro kontrolu autorizace (ACL) na základě rolí uživatelů.
 * Definuje mapování mezi schopnostmi (abilities) a požadovanými rolemi.
 *
 * Třída je navržena jako jednoduchý RBAC (Role-Based Access Control) systém.
 * Všechna pravidla jsou definována na jednom místě pro snadnou správu.
 */
final class Access
{
    /**
     * Mapování schopností na požadované role.
     * Klíč: schopnost (ability) - string identifikátor
     * Hodnota: pole rolí, které mají k této schopnosti přístup
     *
     * TODO: [MAINTENANCE] Při rozšíření aplikace přesunout do konfiguračního souboru nebo databáze
     * TODO: [SECURITY] Přidat podporu pro hierarchii rolí (např. admin má všechna práva mistra)
     * TODO: [FEATURE] Přidat podporu pro kontextová oprávnění (např. 'teams.edit.own')
     */
    private const RULES = [
        'teams.edit' => ['admin', 'mistr'],
        'users.edit' => ['admin', 'mistr'],
        'users.password' => ['admin'],
    ];

    /**
     * Ověří, zda aktuálně přihlášený uživatel má oprávnění k dané schopnosti.
     * Kontroluje přihlášení, existenci pravidla a přiřazení role uživatele.
     *
     * Filozofie: Explicitní deny - pokud schopnost není definována, vrací FALSE.
     * Alternativní přístup: Implicitní deny - vždy vyžadovat explicitní povolení.
     *
     * Očekává:
     * - Funkční Auth třídu pro kontrolu přihlášení a rolí
     * - Konzistentní pojmenování schopností v celé aplikaci
     *
     * TODO: [SECURITY] Přidat caching výsledků kontroly na úrovni requestu pro opakované volání
     * TODO: [FEATURE] Přidat parametrizované schopnosti (např. 'teams.edit.{$teamId}')
     *
     * @param string $ability Schopnost k ověření (např. 'teams.edit', 'users.delete')
     * @return bool TRUE pokud má uživatel oprávnění, jinak FALSE
     */
    public static function can(string $ability): bool
    {
        // TODO: [PERFORMANCE] Přidat logování neúspěšných pokusů o přístup pro audit
        if (!Auth::check()) {
            return false;
        }

        if (!isset(self::RULES[$ability])) {
            // Filozofie: Explicitní deny - nepovolené schopnosti jsou zakázány
            // Alternativa: return true pro implicitní povolení neznámých schopností
            return false;
        }

        return Auth::hasRole(self::RULES[$ability]);
    }
}