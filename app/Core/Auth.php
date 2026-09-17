<?php declare(strict_types=1);

namespace App\Core;

use App\Models\UserModel;
use DomainException;
use App\Core\Types;

/**
 * Centrální autentizační a autorizační služba.
 * Zajišťuje správu přihlášeného uživatele, kontrolu rolí a multi-tenant kontext.
 *
 * Třída je navržena jako statická služba pro snadný přístup k uživatelským datům
 * v celé aplikaci. Implementuje caching uživatelských dat a podporuje role-switching.
 * @phpstan-import-type UserRow from Types
 * @phpstan-import-type SessionUserRow from Types
 */
class Auth
{
    /**
     * Klíč pro ukládání uživatelských dat v session.
     */
    private const USER_KEY = 'user';

    /** 
     * Cache načteného uživatele z databáze.
     * Zabránění opakovaným dotazům v rámci jednoho requestu.
     * @var UserRow|null
     */
    private static ?array $cachedUser = null;

    // TODO: [SECURITY] Přidat časovou značku poslední aktivity pro timeout session
    // TODO: [PERFORMANCE] Zvážit cacheování rolí a oprávnění na úrovni uživatele


    private static bool $userLoaded = false;


    /* ========================= ZÁKLAD ========================= */

    /**
     * Ověří, zda je uživatel přihlášen.
     * Kontroluje pouze přítomnost user.id v session bez ověření proti databázi.
     *
     * Očekává:
     * - Session je dostupná a inicializovaná
     *
     * @return bool TRUE pokud je uživatel přihlášen, jinak FALSE
     */
    public static function check(): bool
    {
        Session::start();

        $id = Session::get(self::USER_KEY . '.id');
        if (!is_int($id) && !ctype_digit((string)$id)) {
            return false;
        }

        return true;
    }


    /**
     * Vrátí ID přihlášeného uživatele.
     *
     * @return int|null ID uživatele nebo null pokud není přihlášen
     */
    public static function id(): ?int
    {
        Session::start();
        return Session::get(self::USER_KEY . '.id');
    }

    /**
     * Vrátí globální roli uživatele z session.
     * Neprovádí se role-switching (vrátí vždy skutečnou roli).
     *
     * @return string|null Role uživatele nebo null
     */
    public static function role(): ?string
    {
        Session::start();
        return Session::get(self::USER_KEY . '.global_role');
    }

    /**
     * Vrátí název společnosti uživatele.
     *
     * @return string|null Název společnosti nebo null
     */
    public static function company(): ?string
    {
        Session::start();
        return Session::get(self::USER_KEY . '.company_name');
    }

    /**
     * Vrátí ID společnosti uživatele.
     *
     * @return int|null ID společnosti nebo null
     */
    public static function companyId(): ?int
    {
        Session::start();
        return Session::get(self::USER_KEY . '.company_id');
    }

    /**
     * Vrátí kompletní data přihlášeného uživatele z databáze.
     * Používá caching na úrovni requestu.
     *
     * Vedlejší efekty:
     * - Při prvním volání v requestu provede dotaz do databáze
     * - Ukládá data do statické cache
     *
     * TODO: [PERFORMANCE] Přidat cache TTL pro uživatelská data (např. 5 minut)
     * TODO: [SECURITY] Při změně role/oprávnění invalidovat cache
     *
     * @return UserRow|null Data uživatele nebo null pokud není přihlášen
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        if (self::$userLoaded === false) {
            $model = new UserModel();
            $user  = $model->find(self::id());

            $sessionVersion = (int) Session::get(self::USER_KEY . '.session_version', 0);

            if (
                $user === null  ||
                (int)$user['active'] !== 1 ||
                (int)$user['session_version'] !== $sessionVersion
            ) {
                self::logout();
                return null;
            }

            self::$cachedUser = $user;
            self::$userLoaded = true;
        }

        return self::$cachedUser;
    }


    /* ========================= JMÉNO / LABEL ========================= */

    /**
     * Vrátí celé jméno uživatele (first_name + last_name).
     *
     * @return string|null Celé jméno nebo null pokud není přihlášen
     */
    public static function name(): ?string
    {
        $user = self::user();
        if ($user === null ) {
             return null;
        }

        $name = trim($user['first_name'] . ' ' . $user['last_name']);
        return $name !== '' ? $name : null;
    }

    /**
     * Vrátí popisek uživatele pro zobrazení v UI.
     * Obsahuje jméno, efektivní roli a název firmy (tenanta).
     *
     * Příklady:
     * - "Franta Jonáš (mistr, Zateplovačky s.r.o.)"
     * - "Admin (ACME Corp)"
     *
     * @return string|null Formátovaný popisek nebo null pokud není přihlášen
     */
    public static function label(): ?string
    {
        if (!self::check()) {
            return null;
        }

        $name    = self::name();
        $role    = self::effectiveRole();
        $company = self::company();

        $parts = [];

        if ($role !== '' ) {
            $parts[] = $role;
        }

        if ($company !== null && $company !== '') {
            $parts[] = $company;
        }

        $suffix = $parts !== []
                        ? ' (' . implode(', ', $parts) . ')'
                        : '';
        if ($name !== null && $name !== '') {
            return $name . $suffix;
        }

        if ($parts !== []) {
             return ucfirst($parts[0]) . (
                    $company !== null && $company !== ''
                        ? ' (' . $company . ')'
                        : ''
            );
        }

       return null;    
    }

    /* ========================= ROLE ========================= */

    /**
     * Ověří, zda má uživatel některou z požadovaných rolí.
     * Bere v úvahu effective role (role-switching pro adminy).
     *
     * @param array<int, string> $roles Pole rolí k ověření
     * @return bool TRUE pokud má uživatel některou z požadovaných rolí
     */
    public static function hasRole(array $roles): bool
    {
        return in_array(self::effectiveRole(), $roles, true);
    }

    /* ========================= LOGIN / LOGOUT ========================= */

    /**
     * Přihlásí uživatele a uloží jeho data do session.
     * Regeneruje session ID pro prevenci session fixation.
     *
     * Vedlejší efekty:
     * - Regeneruje session ID
     * - Ukládá uživatelská data do session
     * - Resetuje uživatelskou cache
     *
     * TODO: [SECURITY] Přidat logování úspěšných přihlášení
     * TODO: [FEATURE] Přidat možnost "zapamatovat si mě" s dlouhodobou session
     *
     * @param SessionUserRow $userData
     * @return void
     */
    public static function login(array $userData): void
    {
        Session::start();
        Session::regenerate();
        Session::set(self::USER_KEY, $userData);
        AccessLogger::log(AccessLogger::TYPE_LOGIN);
        self::$cachedUser = null;
    }

    /**
     * Odhlásí uživatele a vyčistí session data.
     * Regeneruje session ID pro prevenci session fixation.
     *
     * Vedlejší efekty:
     * - Maže uživatelská data z session
     * - Regeneruje session ID
     * - Resetuje uživatelskou cache
     *
     * TODO: [AUDIT] Přidat logování odhlášení
     *
     * @return void
     */
    public static function logout(): void
    {
        Session::start();
        AccessLogger::log(AccessLogger::TYPE_LOGOUT);
        
        Session::destroy();
        self::$cachedUser = null;
    }

    /* ========================= HELPERY ========================= */

    /**
     * Vrátí efektivní roli uživatele s ohledem na role-switching.
     * Admin může dočasně přepnout na nižší roli pro testování.
     *
     * TODO: [SECURITY] Přidat timeout pro role-switching (automatický návrat po X minutách)
     *
     * @return string Efektivní role uživatele
     */
 public static function effectiveRole(): string
{
    $user = self::user();

    if ($user === null) {
        return 'guest';
    }

    if (
        $user['global_role'] === 'admin' &&
        Session::has('auth.effective_role')
    ) {
        $role = (string) Session::get('auth.effective_role');

        if (array_key_exists($role, Roles::effective())) {
            return $role;
        }
    }

    return $user['global_role'];
}


    /**
     * Ověří, zda má uživatel globální roli (bez ohledu na role-switching).
     * Vhodné pro kontrolu skutečných oprávnění.
     *
     * @param array<int, string> $roles Pole globálních rolí k ověření
     * @return bool TRUE pokud má uživatel některou z požadovaných globálních rolí
     */
    public static function hasGlobalRole(array $roles): bool
    {
        $user = self::user();
        if ($user === null) {
            return false;
        }

        return in_array($user['global_role'], $roles, true);
    }

    /**
     * Zjistí, zda může uživatel přepínat role (pouze admin).
     *
     * @return bool TRUE pokud může uživatel přepínat role
     */
    public static function canSwitchRole(): bool
    {
        return self::hasGlobalRole(['admin']);
    }
    
    /**
     * Vrátí email přihlášeného uživatele.
     *
     * @return string|null Email uživatele nebo null
     */
    public static function email(): ?string
    {
        if (!self::check()) {
            return null;
        }

        return Session::get(self::USER_KEY . '.email');
    }

    /**
     * Vrátí název work databáze pro tenant právě přihlášeného uživatele  
     *
     * @return string|null db_name tenantu uživatele
     */
    
    public static function dbName(): ?string
    {
        if (!self::check()) {
            return null;
        }

        return Session::get(self::USER_KEY . '.db_name');
    }
   /**
     * Vrátí slug tenanta z session.
     * Používá se pro multi-tenant routing a izolaci dat.
     *
     * @return string|null Tenant slug nebo null
     */
    public static function tenantSlug(): ?string
    {
        return Session::get('user.tenant_slug');
    }

    /**
     * Přepne efektivní roli admina na jinou roli.
     * Pouze pro administrátory, pro testování funkcionality s omezenými právy.
     *
     * TODO: [AUDIT] Přidat logování přepnutí rolí pro audit trail
     *
     * @param string $role Cílová role pro přepnutí
     * @throws \DomainException Pokud uživatel nemůže přepínat role nebo je role neplatná
     * @return void
     */
    public static function switchRole(string $role): void
    {
        Session::start();

        if (!self::canSwitchRole()) {
            throw new DomainException('Pouze admin může přepínat role');
        }

        // reset = návrat na admina
        if ($role === 'admin' || $role === 'reset') {
            Session::forget('auth.effective_role');
            return;
        }

        // povolené role
        if (!array_key_exists($role, Roles::effective())) {
            throw new DomainException('Neplatná role');
        }

        Session::set('auth.effective_role', $role); 
    }
}