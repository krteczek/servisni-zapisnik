<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\AccessLogModel;
use App\Services\Guards\BanService;

/**
 * Statická služba pro logování přístupů k aplikaci a detekci potenciálního zneužití.
 * Slouží k evidenci požadavků a monitorování podezřelé aktivity
 * (např. opakované chyby 403/404).
 *
 * Vedlejší efekty:
 * - Zapíše záznam do databázové tabulky přístupů
 * - Může nastavit flash zprávu uživateli při detekci podezřelé aktivity
 * - Může zablokovat přístup a zneplatnit aktuální session
 */
final class AccessLogger
{
    public const TYPE_403 = 403; // forbidden
    public const TYPE_404 = 404; // not found
    public const TYPE_LOGIN = 100; // login
    public const TYPE_LOGOUT = 999; // logout

    /**
     * @return array<int, int>
     */
    public static function all(): array
    {
        return [
            self::TYPE_403,
            self::TYPE_404,
            self::TYPE_LOGIN,
            self::TYPE_LOGOUT,
        ];
    }

    /**
     * Zapíše záznam o přístupu do logu a provede kontrolu na zneužití.
     * Metoda je navržena jako fail-safe – žádná výjimka nesmí způsobit pád aplikace.
     *
     * Očekává:
     * - Dostupné $_SERVER proměnné
     * - Funkční databázové připojení
     * - Přítomnost Auth třídy pro získání ID přihlášeného uživatele
     *
     * TODO: [SECURITY] Přidat logování HTTP referer pro lepší analýzu útoků
     * TODO: [PERFORMANCE] Při vysokém vytížení zvážit batchování logů
     * TODO: [FEATURE] Přidat možnost konfigurovat logované typy událostí
     *
     * @param int $type Typ logované události
     * @return void
     */
    public static function log(int $type): void
    {
        try {
            $model = new AccessLogModel();
            $userId = Auth::check() ? Auth::id() : null;
            $ip = Request::ip();
            $us = Request::ua();
            $companyId = Auth::check() ? Auth::companyId() : null;

            $model->log([
                'user_id' => $userId,
                'ip_address' => $ip,
                'type' => $type,
                'path' => $_SERVER['REQUEST_URI'] ?? '',
                'method' => $_SERVER['REQUEST_METHOD'] ?? '',
                'user_agent' => $us,
                'company_id' => $companyId,
            ]);

            if ($type === self::TYPE_403 || $type === self::TYPE_404) {
                self::detectAbuse(
                    model: $model,
                    type: $type,
                    ip: $ip
                );
            }
        } catch (\Throwable) {
            // TODO: [OBSERVABILITY] Přidat fallback logování do souboru/syslog,
            // když DB selže. Logování NIKDY nesmí shodit aplikaci.
        }
    }

    /**
     * Detekuje potenciální zneužití na základě opakovaných chybových stavů.
     * Kontroluje frekvenci chyb 403/404 z dané IP adresy.
     *
     * Při překročení varovného limitu nastaví flash zprávu.
     * Při překročení ban limitu zablokuje fingerprint a zneplatní
     * případnou aktuální uživatelskou session.
     *
     * @param AccessLogModel $model Instance modelu pro dotazy na logy
     * @param int $type Typ události 403 nebo 404
     * @param string $ip IP adresa klienta
     * @return void
     */
    private static function detectAbuse(
        AccessLogModel $model,
        int $type,
        string $ip
    ): void {
        $conf = Config::get('rateLimits');

        $count = $model->countRecent(
            type: $type,
            ip: $ip,
            minutes: $conf['SCANNING_WARNING']['time']
        );

        if ($count >= $conf['SCANNING_WARNING']['rate']) {
            Flash::error(
                'Bylo zaznamenáno opakované neplatné chování. '
                . 'Pokračování může vést k omezení účtu.'
            );
        }

        $count = $model->countRecent(
            type: $type,
            ip: $ip,
            minutes: $conf['SCANNING_BAN']['time']
        );

        if ($count >= $conf['SCANNING_BAN']['rate']) {
            BanService::banLogout('SCANNING_BAN');

            Flash::error(
                'Bylo zaznamenáno opakované neplatné chování. '
                . 'Váš přístup je dočasně zablokován.'
            );
        }
    }
}