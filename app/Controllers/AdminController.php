<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Config;
use App\Core\Url;
use DomainException;

final class AdminController extends Controller
{
    /**
     * Přepnutí pohledu role (jen admin)
     * /admin/switch-role/{role}
     */
public function switchRole(string $role): void
{
    $user = Auth::user();

    if (!$user || $user['global_role'] !== 'admin') {
        throw new DomainException('Pouze admin může přepínat role');
    }

    // RESET
    if ($role === 'reset') {
        unset($_SESSION['effective_role']);
        Url::redirect('/');
    }

    $roles = array_keys(Config::get('roles')['roles']);

    if (!in_array($role, $roles, true)) {
        throw new DomainException('Neplatná role');
    }

    $_SESSION['effective_role'] = $role;

    Url::redirect('/');
}
}
