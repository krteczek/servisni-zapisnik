<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;

class DashboardController extends Controller
{

public function index(): string
    {
        //$this->view->title = 'Dashboard';

        return $this->render('dashboard/index');
    }
    public function admin(): string
    {
        return $this->render('dashboard/admin');
    }
public function root(): void
{
    \App\Core\Url::redirect('/dashboard/#main');
}

}