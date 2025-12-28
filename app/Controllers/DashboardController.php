<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;

class DashboardController extends Controller
{
    public function index(): string
    {
        $user = Auth::user();

        return $this->view('dashboard/index', [
            'user' => $user
        ]);
    }
}