<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\UserModel;

class Auth
{
    private const USER_KEY = 'user';

    /** cache načteného uživatele */
    private static ?array $cachedUser = null;

    /* =========================
       ZÁKLAD
       ========================= */

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

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        if (self::$cachedUser === null) {
            $model = new UserModel();
            self::$cachedUser = $model->findById(self::id());
        }

        return self::$cachedUser;
    }

    /* =========================
       JMÉNO / LABEL
       ========================= */

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

    /* =========================
       ROLE
       ========================= */

public static function hasRole(array $roles): bool
{
    $role = self::effectiveRole();

    return in_array($role, $roles, true);
}

    /* =========================
       LOGIN / LOGOUT
       ========================= */

    public static function login(array $userData): void
    {
        Session::start();
        Session::regenerate(); // session fixation

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

    /* =========================
       AKTUALIZACE SESSION
       ========================= */

    public static function update(array $data): void
    {
        if (!self::check()) {
            return;
        }

        Session::start();

        $current = Session::get(self::USER_KEY, []);
        Session::set(self::USER_KEY, array_merge($current, $data));

        self::$cachedUser = null;
    }

    /* =========================
       HELPERY
       ========================= */

    public static function isGuest(): bool
    {
        return !self::check();
    }

    public static function redirectIfGuest(string $to = '/login'): void
    {
        if (self::isGuest()) {
            Url::redirect($to);
        }
    }

    public static function redirectIfLoggedIn(string $to = '/'): void
    {
        if (self::check()) {
            Url::redirect($to);
        }
    }
    
        public static function effectiveRole(): string
    {
        $user = self::user();

        if (!$user) {
            return '';
        }

        // admin může simulovat
        if (
            $user['global_role'] === 'admin'
            && isset($_SESSION['effective_role'])
        ) {
            return $_SESSION['effective_role'];
        }

        return $user['global_role'];
    }
    
    public static function hasGlobalRole(array $roles): bool
{
    $user = self::user();

    if (!$user) {
        return false;
    }

    if ($user['global_role'] === 'admin') {
        return true;
    }

    return in_array($user['global_role'], $roles, true);
}

}