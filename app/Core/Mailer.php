<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Služba pro odesílání systémových e-mailů (aktivace účtu, obnova hesla).
 * Generuje české textové e-maily s osobními odkazy.
 *
 * Třída používá PHP mail() funkci, což je vhodné pro malé objemy e-mailů.
 * Pro produkční nasazení s vyšší zátěží zvažte použití SMTP nebo API služeb.
 */
final class Mailer
{
    // TODO: [CONFIG] Přesunout e-mailové adresy (From, Reply-To) do konfigurace
    // TODO: [I18N] Přidat podporu pro více jazyků e-mailů podle uživatele

    /**
     * Odešle e-mail s aktivačním odkazem pro nový uživatelský účet.
     * Obsahuje personalizovaný odkaz s časově omezeným tokenem.
     *
     * Vedlejší efekty:
     * - Odesílá e-mail přes PHP mail() funkci
     * - Může vyhodit výjimku při selhání odeslání
     *
     * TODO: [SECURITY] Přidat rate limiting na odesílání aktivačních e-mailů
     * TODO: [AUDIT] Logovat odeslání aktivačních e-mailů pro debugging
     *
     * @param string $email Cílová e-mailová adresa
     * @param string $token Bezpečnostní token pro aktivaci
     * @param string $tenant Název tenanta/firmy pro personalizaci
     * @return void
     * @throws \RuntimeException Pokud se nepodaří e-mail odeslat
     */
    public static function sendActivation(
        string $email,
        string $token,
        string $tenant
    ): void {
        $link = Url::base() . Url::to("/activate?token=" . urlencode($token));

        $subject = 'Aktivace účtu';
        $message = <<<TEXT
Dobrý den,

byl vám vytvořen účet v aplikaci Servisní zápisník (firma: {$tenant}).

Pro aktivaci účtu a nastavení hesla klikněte na odkaz:
{$link}

Platnost odkazu je časově omezená.

Pokud jste tuto zprávu nečekali, ignorujte ji.
TEXT;

        self::send($email, $subject, $message);
    }

    /**
     * Odešle e-mail s odkazem pro obnovu zapomenutého hesla.
     * Obsahuje jednorázový token s omezenou platností.
     *
     * Vedlejší efekty:
     * - Odesílá e-mail přes PHP mail() funkci
     * - Může vyhodit výjimku při selhání odeslání
     *
     * TODO: [SECURITY] Zneplatnit všechny předchozí reset tokeny při odeslání nového
     * TODO: [UX] Přidat informaci o platnosti tokenu (např. 1 hodina)
     *
     * @param string $email Cílová e-mailová adresa
     * @param string $token Bezpečnostní token pro reset hesla
     * @param string $tenant Název tenanta/firmy pro personalizaci
     * @return void
     * @throws \RuntimeException Pokud se nepodaří e-mail odeslat
     */
    public static function sendResetPassword(
        string $email,
        string $token,
        string $tenant
    ): void {
        $link = Url::base() . Url::to("/reset-password?token=" . urlencode($token));

        $subject = 'Obnova hesla';
        $message = <<<TEXT
Dobrý den,

byla vyžádána změna hesla k vašemu účtu v aplikaci Servisní zápisník (firma: {$tenant}).

Pro nastavení nového hesla klikněte na odkaz:
{$link}

Pokud jste o změnu nežádali, můžete tento e-mail ignorovat.
TEXT;

        self::send($email, $subject, $message);
    }

    /* =========================
       LOW-LEVEL SEND
       ========================= */

    /**
     * Nízkoúrovňová metoda pro odeslání e-mailu pomocí PHP mail().
     * Konfiguruje hlavičky pro české znaky a správné fungování.
     *
     * Očekává:
     * - Funkční mail server (Sendmail, SMTP, atd.)
     * - Platné e-mailové adresy
     * - UTF-8 kódování textů
     *
     * TODO: [RELIABILITY] Přidat SMTP fallback pokud mail() selže
     * TODO: [PERFORMANCE] Zvážit queue pro odesílání e-mailů (async)
     * TODO: [SECURITY] Validovat e-mailové adresy před odesláním
     *
     * @param string $to Cílová e-mailová adresa
     * @param string $subject Předmět e-mailu
     * @param string $message Tělo e-mailu (plain text)
     * @return void
     * @throws \RuntimeException Pokud mail() vrátí false
     */
    private static function send(
        string $to,
        string $subject,
        string $message
    ): void {
        $headers = [
            'From: Bó - Servisní zápisník <noreply@krteczek.cz>',
            'Reply-To: podpora@krteczek.cz',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8'
        ];

        // TODO: [DEBUG] Přidat logování odeslaných e-mailů v dev prostředí
        if (!mail($to, $subject, $message, implode("\r\n", $headers))) {
            // TODO: [OBSERVABILITY] Logovat detaily selhání (error_get_last())
            throw new \RuntimeException('Odeslání e-mailu selhalo.');
        }
    }
}