<?php declare(strict_types=1);

namespace App\Core;

use App\Models\UserModel;

class Auth
{
    private const USER_KEY = 'user';

    /** cache načteného uživatele */
    private static ?array $cachedUser = null;

    /* ========================= ZÁKLAD ========================= */

    public static function check(): bool
    {
        Session::start();
        return (bool) Session::get(self::USER_KEY . '.id');
    }

    public static function id(): ?int
    {
        Session::start();
        return Session::get(self::USER_KEY . '.id');
    }

    public static function role(): ?string
    {
        Session::start();
        return Session::get(self::USER_KEY . '.global_role');
    }

    public static function companyId(): ?int
    {
        Session::start();
        return Session::get(self::USER_KEY . '.company_id');
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        if (self::$cachedUser === null) {
            $model = new UserModel();
            self::$cachedUser = $model->find(self::id());
        }

        return self::$cachedUser;
    }

    /* ========================= JMÉNO / LABEL ========================= */

    public static function name(): ?string
    {
        if (!self::check()) {
            return null;
        }

        Session::start();
        $first = Session::get(self::USER_KEY . '.first_name', '');
        $last  = Session::get(self::USER_KEY . '.last_name', '');

        $name = trim($first . ' ' . $last);
        return $name !== '' ? $name : null;
    }

    public static function label(): ?string
    {
        if (!self::check()) {
            return null;
        }

        $name = self::name();
        $role = self::role();

        return $name
            ? sprintf('%s (%s)', $name, $role)
            : ($role ? ucfirst($role) : null);
    }

    /* ========================= ROLE ========================= */

    public static function hasRole(array $roles): bool
    {
        return in_array(self::effectiveRole(), $roles, true);
    }

    /* ========================= LOGIN / LOGOUT ========================= */

    public static function login(array $userData): void
    {
        Session::start();
        Session::regenerate();
        Session::set(self::USER_KEY, $userData);
        self::$cachedUser = null;
    }

    public static function logout(): void
    {
        Session::start();
        Session::forget(self::USER_KEY);
        Session::regenerate();
        self::$cachedUser = null;
    }

    /* ========================= HELPERY ========================= */

public static function effectiveRole(): string
{
    $user = self::user();
    if (!$user) {
        return '';
    }

    // impersonace jen pro admina
    if (
        $user['global_role'] === 'admin' &&
        Session::has('auth.effective_role')
    ) {
        $role = (string) Session::get('auth.effective_role');

        // pouze povolené role
        if (array_key_exists($role, Roles::effective())) {
            return $role;
        }
    }

    return $user['global_role'];
}

    public static function hasGlobalRole(array $roles): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }

        return in_array($user['global_role'], $roles, true);
    }

    public static function canSwitchRole(): bool
    {
        return self::hasGlobalRole(['admin']);
    }
    
public static function email(): ?string
{
    if (!self::check()) {
        return null;
    }

    return Session::get(self::USER_KEY . '.email');
}
}
