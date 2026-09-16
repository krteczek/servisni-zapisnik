<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Url;


final class AdminController extends Controller
{
    /**
     * Přepnutí pohledu role (jen admin)
     * /admin/switch-role/{role}
     */

public function switchRole(string $role): void
{
    Auth::switchRole($role);
    Url::back();
}

}
