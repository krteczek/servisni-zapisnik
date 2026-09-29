<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\AccessLogger;
use App\Validators\Validator;

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

    protected Validator $validator;

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
        //$this->view->errors ??= [];
        //$this->view->data   ??= [];

        // obecná validační třída
        $this->validator = new Validator();

        // TODO: [MAINTENANCE] Přesunout logiku přepínání databází do middleware nebo samostatné služby
        // TODO: [PERFORMANCE] Zvážit cachování databázového připojení na úrovni requestu
        if (Auth::check()) {
            $db = Session::get('user.db_name');
            if ($db !== null && $db !== '') {
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
        return (string) ob_get_clean();
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
        //$this->view->errors[$field][] = $message;
        $this->validator->addError($field, $message);
    }

    /**
     * Zjistí, zda byly zaznamenány nějaké chyby.
     *
     * @return bool TRUE pokud existují nějaké chyby, jinak FALSE
     */
    protected function hasErrors(): bool
    {
        //return $this->view->errors !== [];
        return $this->validator->hasErrors();
    }

    /**
     * Získá chybovou zprávu pro daný klíč.
     * Pokud chybová zpráva neexistuje, vrátí prázdný string.
     * TODO: [FEATURE] Přidat možnost získat všechny chyby jako pole
     *
     * @param string $key
     * @return list<string>|string
     */
    public function getError($key): array|string
    {
        return $this->validator->getError($key);
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

        $this->validator->maxLength($field, $value, $max, $label);
    }

    public function isValid(): bool
    {
        return $this->validator->isValid();
    }

 
    /**
     * @return array<string, list<string>>
     */
    public function getErrors(): array
    {
        return $this->validator->getErrors();
    }

 
    public function required(
        string $field,
        string $value,
        string $message
    ): void {
        if ($value === '') {
            $this->validator->required($field, $value, $message);
        }
    }


/**
 * ochrana proti přepisování hodnot jako id a zkoušení 
 * měnit jiné údaje než které jsou ke změně vybrané...
 * 
 * Nastaví jednorázovou hodnotu pro session kontrolu.
 *
 * @param string $key Klíč kontroly.
 * @param int|string $value Hodnota kontroly.
 */
protected function setSessionCheck(
    string $key,
    int|string $value
): void {
    Session::set('_checks.' . $key, $value);
}

/**
 * Ověří a spotřebuje jednorázovou hodnotu ze session.
 *
 * @param string $key Klíč kontroly.
 * @param int|string $value Očekávaná hodnota.
 * @return bool True pouze pokud hodnota v session odpovídá.
 */
protected function confirmSessionCheck(
    string $key,
    int|string $value,
    string $redirect
): bool {
    $sessionKey = '_checks.' . $key;
    $stored = Session::get($sessionKey);

    Session::forget($sessionKey);

    if($stored === $value) {
        return true;
    
    }

    /** nejspíše pokus o podstrčení cizích dat */
    Flash::error('Nesourodá vstupní data. Vyberte si ze seznamu, kterou položku chcete upravit...');
    Url::redirect($redirect);
}
}