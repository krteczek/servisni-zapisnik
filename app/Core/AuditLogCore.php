<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;

/**
 * Základní služba pro auditování změn v aplikaci.
 * Ukládá záznamy o uživatelských akcích pro budoucí audit a forenzní analýzu.
 *
 * Třída implementuje "best-effort" přístup - selhání auditování nesmí ovlivnit
 * hlavní funkcionalitu aplikace.
 */
final class AuditLogCore
{
    /**
     * Vytvoří záznam v audit logu o provedené akci.
     * Shromažďuje kontextové informace (kdo, kdy, co, z jaké IP atd.).
     *
     * Vedlejší efekty:
     * - Zapíše záznam do databázové tabulky `audit_logs`
     * - Může serializovat pole změn do JSON formátu
     *
     * Očekává:
     * - Dostupné $_SERVER proměnné (REMOTE_ADDR, HTTP_USER_AGENT)
     * - Funkční Auth třídu pro získání informací o uživateli
     * - Konfiguraci 'audit.enabled' pro povolení/vypnutí logování
     *
     * TODO: [PERFORMANCE] Při vysokém vytížení zvážit batchování audit záznamů
     * TODO: [OBSERVABILITY] Přidat metrik pro počet záznamů/chyb do monitorovacího systému
     * TODO: [FEATURE] Přidat podporu pro logování před/po stavu entity (snapshoty)
     *
     * @param string $entity Typ entity (např. 'user', 'team', 'order')
     * @param int|string|null $entityId ID entity nebo null pro entitu bez ID
     * @param string $action Provedená akce (např. 'create', 'update', 'delete', 'login', 'logout')
     * @param array $diff Pole změn ve formátu ['field' => ['old' => '...', 'new' => '...']]
     * @return void
     */
    public static function log(
        string $entity,
        int|string|null $entityId,
        string $action,
        array $diff = []
    ): void {
        if (!self::isEnabled()) {
            return;
        }

        try {
            self::logInsert([
                'company_id'  => Auth::companyId(),
                'user_id'     => Auth::id(),
                'user_email'  => Auth::email(),
                'action'      => $action,
                'entity'      => $entity,
                'entity_id'   => $entityId,
                'diff'        => $diff ? json_encode($diff, JSON_THROW_ON_ERROR) : null,
                'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);
        } catch (Throwable $e) {
            // TODO: [OBSERVABILITY] Přidat logování do souboru/syslog při selhání DB zápisu
            // audit je BEST-EFFORT – NESMÍ nikdy shodit aplikaci
            // ticho je zde záměrné
        }
    }

    /**
     * Provede INSERT záznamu do databázové tabulky audit_logs.
     * Používá admin databázové připojení pro centralizované ukládání logů.
     *
     * Očekává:
     * - Existující tabulku `audit_logs` s odpovídajícím schématem
     * - Dostupné Database::admin() připojení
     *
     * TODO: [MAINTENANCE] Po migraci na novou verzi DB přidat ukázku schématu tabulky jako komentář
     * TODO: [PERFORMANCE] Zvážit použití UPSERT pro aktualizaci existujících záznamů
     *
     * @param array $row Asociativní pole hodnot pro INSERT
     * @return void
     */
    private static function logInsert(array $row): void
    {
        $db = Database::admin();

        $sql = '
            INSERT INTO audit_logs
            (company_id, user_id, user_email, action, entity, entity_id, diff, ip_address, user_agent)
            VALUES
            (:company_id, :user_id, :user_email, :action, :entity, :entity_id, :diff, :ip_address, :user_agent)
        ';

        $stmt = $db->prepare($sql);
        $stmt->execute($row);
    }

    /**
     * Zjistí, zda je audit logování povoleno v konfiguraci.
     *
     * TODO: [FEATURE] Přidat možnost povolení/vypnutí audit logování na úrovni tenant/company
     * TODO: [CONFIG] Přidat konfiguraci pro různé úrovně logování (např. pouze CRUD operace)
     *
     * @return bool TRUE pokud je audit logování povoleno, jinak FALSE
     */
    private static function isEnabled(): bool
    {
        return Config::get('audit.enabled', true);
    }
}