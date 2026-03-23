<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\AccessLogger;

/**
 * Abstraktní základní třída pro všechny controllery aplikace.
 * Poskytuje společnou funkcionalitu pro práci s view, CSRF ochranu, správu chyb
 * a základní HTTP response handling.
 *
 * Při konstrukci automaticky nastavuje pracovní databázi podle přihlášeného uživatele.
 */
abstract class Controller
{
    /**
     * Kontext pro předání dat do view.
     *
     * @var ViewContext
     */
    protected ViewContext $view;

    /**
     * Inicializuje controller a nastaví pracovní prostředí.
     *
     * Vedlejší efekty:
     * - Inicializuje pole pro chyby a data v view kontextu
     * - Pokud je uživatel přihlášen, přepne databázové připojení na jeho tenant databázi
     * - Mění stav Session a Database připojení
     *
     * @param ViewContext $view Kontext pro předání dat do view
     */
    public function __construct(ViewContext $view)
    {
        $this->view = $view;
        $this->view->errors ??= [];
        $this->view->data   ??= [];

        // TODO: [MAINTENANCE] Přesunout logiku přepínání databází do middleware nebo samostatné služby
        // TODO: [PERFORMANCE] Zvážit cachování databázového připojení na úrovni requestu
        if (Auth::check()) {
            $db = Session::get('user.db_name');
            if ($db) {
                Database::useWorkDatabase($db);
            }
        }
    }

    /**
     * Vykreslí PHP šablonu a vrátí její obsah jako string.
     * Používá output buffering pro zachycení výstupu šablony.
     *
     * Očekává:
     * - Šablona existuje v adresáři `../Views/` s příponou `.php`
     *
     * @param string $template Název šablony (bez přípony .php)
     * @return string HTML obsah vykreslené šablony
     */
    protected function render(string $template): string
    {
        $view = $this->view;

        ob_start();
        require __DIR__ . '/../Views/' . $template . '.php';
        return ob_get_clean();
    }

    /* =========================
       CSRF
       ========================= */

    /**
     * Vygeneruje HTML hidden input s CSRF tokenem pro použití ve formulářích.
     *
     * @return string HTML kód `<input type="hidden" name="_token" value="...">`
     */
    protected function csrfField(): string
    {
        return Csrf::getField();
    }

    /**
     * Ověří platnost CSRF tokenu z POST dat.
     * Pokud token není platný, přidá chybu do error stacku.
     *
     * Vedlejší efekty:
     * - Přidá chybu do `$this->view->errors` pokud validace selže
     *
     * @throws \RuntimeException Pokud CSRF token chybí nebo je neplatný
     * @return void
     */
    protected function checkCsrf(): void
    {
        if (!Csrf::verify($_POST['_token'] ?? '')) {
            $this->addError('_csrf', 'Platnost formuláře vypršela. Zkuste jej odeslat znovu.');
        }
    }

    /* =========================
       ERRORS
       ========================= */

    /**
     * Přidá chybovou zprávu k určitému poli formuláře.
     *
     * Vedlejší efekty:
     * - Mění stav `$this->view->errors`
     *
     * @param string $field Název pole formuláře
     * @param string $message Text chybové zprávy
     * @return void
     */
    protected function addError(string $field, string $message): void
    {
        $this->view->errors[$field][] = $message;
    }

    /**
     * Zjistí, zda byly zaznamenány nějaké chyby.
     *
     * @return bool TRUE pokud existují nějaké chyby, jinak FALSE
     */
    protected function hasErrors(): bool
    {
        return !empty($this->view->errors);
    }
    public function getError($key): bool
    {
        return $this->view->errors[$key] ?? '';
    }
    /* =========================
       COMMON PAGES
       ========================= */

    /**
     * Vykreslí stránku 403 - Přístup zakázán.
     * Loguje pokus o neoprávněný přístup.
     *
     * Vedlejší efekty:
     * - Nastaví HTTP status code 403
     * - Loguje událost přes AccessLogger
     *
     * @return string HTML obsah stránky 403
     */
    public function forbidden(): string
    {
        AccessLogger::log(403);
        http_response_code(403);
        $this->view->title = '403 – Přístup zakázán';
        return $this->render('errors/403');
    }

    /**
     * Vykreslí stránku 404 - Stránka nenalezena.
     * Loguje pokus o přístup k neexistujícímu obsahu.
     *
     * Vedlejší efekty:
     * - Nastaví HTTP status code 404
     * - Loguje událost přes AccessLogger
     *
     * @return string HTML obsah stránky 404
     */
    public function notFound(): string
    {
        AccessLogger::log(404);
        http_response_code(404);
        $this->view->title = '404 – Stránka nenalezena';
        return $this->render('errors/404');
    }

    /**
     * Validuje maximální délku textového pole.
     * Pokud hodnota překračuje maximální délku, přidá chybu.
     *
     * Vedlejší efekty:
     * - Může přidat chybu do `$this->view->errors`
     *
     * TODO: [FEATURE] Přidat podobné validační metody pro minLength, required, email format, atd.
     *
     * @param string $field Název pole pro chybovou zprávu
     * @param string|null $value Hodnota k validaci (může být NULL)
     * @param int $max Maximální povolený počet znaků
     * @param string $label Popisný název pole pro chybovou zprávu
     * @return void
     */
    protected function maxLength(
        string $field,
        ?string $value,
        int $max,
        string $label
    ): void {
        if ($value === null) {
            return;
        }

        if (mb_strlen($value, 'UTF-8') > $max) {
            $this->addError(
                $field,
                "{$label} může mít maximálně {$max} znaků"
            );
        }
    }
}