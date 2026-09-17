<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;
use \App\Models\AuditLogModel;

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
     * @param array<string, mixed> $diff Pole změn ve formátu ['field' => ['old' => '...', 'new' => '...']]
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
        $diffJson = null;

        if ($diff !== []) {
           try {
                   $diffJson = json_encode($diff, JSON_THROW_ON_ERROR);
           } catch (Throwable) {
                   $diffJson = null;
           }
}
        try {
            (new AuditLogModel())->insertLog([
                'company_id'  => Auth::companyId(),
                'user_id'     => Auth::id(),
                'user_email'  => Auth::email(),
                'action'      => $action,
                'entity'      => $entity,
                'entity_id'   => $entityId,
                'diff'        => $diffJson,
                'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);
        } catch (Throwable $e) {
            // TODO: [OBSERVABILITY] Přidat logování do souboru/syslog při selhání DB zápisu
            // audit je BEST-EFFORT – NESMÍ nikdy shodit aplikaci
            // ticho je zde záměrné
            error_log('AUDIT CORE FAIL: ' . $e->getMessage());
				LoggerHolder::get()->error('AuditLogCore failed', [
				    'message'   => $e->getMessage(),
				    'file'      => $e->getFile(),
				    'line'      => $e->getLine(),
				    'trace'     => $e->getTraceAsString(),

		          'company_id'  => Auth::companyId() ?? TenantContext::get(),
                'user_id'     => Auth::id(),
                'user_email'  => Auth::email(),
                'action'      => $action,
                'entity'      => $entity,
                'entity_id'   => $entityId,
                'diff'        => $diffJson,
                'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
				]);
        }
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