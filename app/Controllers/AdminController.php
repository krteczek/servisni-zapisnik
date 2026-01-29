<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Config;
use App\Core\Url;
use DomainException;
use App\Core\Session;

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

    // RESET = návrat do admina
    if ($role === 'reset' || $role === 'admin') {
        Session::forget('auth.effective_role');
        Url::redirect('/');
    }

    $roles = array_keys(Config::get('roles')['roles']);

    if (!in_array($role, $roles, true)) {
        throw new DomainException('Neplatná role');
    }

    Session::set('auth.effective_role', $role);

    Url::redirect('/');
}

}
