<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Session;

/**
 * Zjednodušená služba pro práci s flash messages (jednorázové notifikace).
 * Poskytuje typované metody pro různé úrovně zpráv s jednotným API.
 *
 * Flash messages jsou dostupné pouze pro následující request a pak se automaticky mažou.
 * Používají se pro informování uživatele o výsledku operací (úspěch, chyba, info).
 */
final class Flash
{
    // TODO: [FEATURE] Přidat podporu pro více flash messages stejného typu (array)
    // TODO: [UX] Přidat podporu pro HTML ve flash messages (s escape)

    /**
     * Uloží úspěšnou flash zprávu pro zobrazení uživateli.
     * Typicky používáno po úspěšném dokončení operace (vytvoření, úprava, smazání).
     *
     * Vedlejší efekty:
     * - Ukládá zprávu do session pod klíčem 'success'
     *
     * TODO: [I18N] Přidat podporu pro lokalizaci flash messages
     *
     * @param string $msg Text zprávy
     * @return void
     */
    public static function success(string $msg): void
    {
        Session::flash('success', $msg);
    }

    /**
     * Uloží chybovou flash zprávu pro zobrazení uživateli.
     * Typicky používáno při neúspěšné operaci nebo validaci formuláře.
     *
     * Vedlejší efekty:
     * - Ukládá zprávu do session pod klíčem 'error'
     *
     * TODO: [SECURITY] Validovat, že zpráva neobsahuje potenciálně nebezpečné HTML/JS
     *
     * @param string $msg Text zprávy
     * @return void
     */
    public static function error(string $msg): void
    {
        Session::flash('error', $msg);
    }

    /**
     * Uloží informační flash zprávu pro zobrazení uživateli.
     * Typicky používáno pro neutrální informace nebo upozornění.
     *
     * Vedlejší efekty:
     * - Ukládá zprávu do session pod klíčem 'info'
     *
     * TODO: [UX] Přidat další úrovně zpráv (warning, danger, primary, secondary)
     *
     * @param string $msg Text zprávy
     * @return void
     */
    public static function info(string $msg): void
    {
        Session::flash('info', $msg);
    }
}