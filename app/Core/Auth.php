<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\UserModel;

class Auth
{
    private const USER_KEY        = 'user';
    private const PERMISSIONS_KEY = 'permissions';

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
       ROLE / PERMISSION
       ========================= */

    public static function hasRole(array $roles): bool
    {
        if (!self::check()) {
            return false;
        }

        return in_array(self::role(), $roles, true);
    }

    public static function can(string $permission): bool
    {
        Session::start();
        $permissions = Session::get(self::PERMISSIONS_KEY, []);
        return in_array($permission, $permissions, true);
    }

    /* =========================
       LOGIN / LOGOUT
       ========================= */

    public static function login(array $userData, array $permissions = []): void
    {
        Session::start();
        Session::regenerate(); // session fixation

        Session::set(self::USER_KEY, $userData);
        Session::set(self::PERMISSIONS_KEY, $permissions);

        self::$cachedUser = null;
    }

    public static function logout(): void
    {
        Session::start();

        Session::forget(self::USER_KEY);
        Session::forget(self::PERMISSIONS_KEY);

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

    public static function updatePermissions(array $permissions): void
    {
        Session::start();
        Session::set(self::PERMISSIONS_KEY, $permissions);
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
            header('Location: ' . $to);
            exit;
        }
    }

    public static function redirectIfLoggedIn(string $to = '/'): void
    {
        if (self::check()) {
            header('Location: ' . $to);
            exit;
        }
    }
}
