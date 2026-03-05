<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\AccessLogModel;

/**
 * Statická služba pro logování přístupů k aplikaci a detekci potenciálního zneužití.
 * Slouží k evidenci požadavků a monitorování podezřelé aktivity (např. opakované chyby 403/404).
 *
 * Vedlejší efekty:
 * - Zapíše záznam do databázové tabulky přístupů
 * - Může nastavit flash zprávu uživateli při detekci podezřelé aktivity
 * - Mění stav databáze a session (flash messages)
 */
final class AccessLogger
{
    /**
     * Zapíše záznam o přístupu do logu a provede kontrolu na zneužití.
     * Metoda je navržena jako fail-safe – žádná výjimka nesmí způsobit pád aplikace.
     *
     * Očekává:
     * - Dostupné $_SERVER proměnné (REMOTE_ADDR, REQUEST_URI, REQUEST_METHOD, HTTP_USER_AGENT)
     * - Funkční databázové připojení
     * - Přítomnost Auth třídy pro získání ID přihlášeného uživatele
     *
     * TODO: [SECURITY] Přidat logování HTTP referer pro lepší analýzu útoků
     * TODO: [PERFORMANCE] Při vysokém vytížení zvážit batchování logů nebo použití asynchronního zápisu
     * TODO: [FEATURE] Přidat možnost konfigurovat logované typy událostí (config)
     *
     * @param string $type Typ logované události ('403', '404', 'login', 'logout', atd.)
     * @return void
     */
    public static function log(string $type): void
    {
        try {
            $model = new AccessLogModel();

            $userId = Auth::check() ? Auth::id() : null;
            $ip     = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

            $model->log([
                'user_id'    => $userId,
                'ip_address' => $ip,
                'type'       => $type,
                'path'       => $_SERVER['REQUEST_URI'] ?? '',
                'method'     => $_SERVER['REQUEST_METHOD'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);

            self::detectAbuse($model, $type, $ip, $userId);

        } catch (\Throwable) {
            // TODO: [OBSERVABILITY] Přidat fallback logování do souboru/syslog když DB selže
            // logování NIKDY nesmí shodit aplikaci
        }
    }

    /**
     * Detekuje potenciální zneužití na základě opakovaných chybových stavů.
     * Kontroluje frekvenci chyb 403/404 z dané IP adresy nebo uživatele.
     *
     * Vedlejší efekty:
     * - Nastaví flash varování uživateli při překročení limitu
     *
     * TODO: [SECURITY] Při překročení limitu 10 pokusů za 10 minut zablokovat IP na 30 minut
     * TODO: [FEATURE] Přidat notifikaci administrátorovi při detekci útoku DDoS/brute force
     * TODO: [CONFIG] Přenést limity (5 pokusů/10 minut) do konfigurace
     *
     * @param AccessLogModel $model Instance modelu pro dotazy na logy
     * @param string $type Typ události
     * @param string $ip IP adresa klienta
     * @param int|null $userId ID přihlášeného uživatele nebo null
     * @return void
     */
    private static function detectAbuse(
        AccessLogModel $model,
        string $type,
        string $ip,
        ?int $userId
    ): void {
        if (!in_array($type, ['403', '404'], true)) {
            return;
        }

        $count = $model->countRecent($type, $ip, 10);

        if ($count >= 5 && $userId !== null) {
            Flash::error(
                'Bylo zaznamenáno opakované neplatné chování. '
                . 'Pokračování může vést k omezení účtu.'
            );
        }
    }
}