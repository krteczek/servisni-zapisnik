<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

class AuthMiddleware
{
    public function handle(): bool
    {
        return Auth::check();
    }
}
