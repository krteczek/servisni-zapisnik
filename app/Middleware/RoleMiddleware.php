<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

class RoleMiddleware
{
    public function __construct(
        private array $roles
    ) {}

    public function handle(): bool
    {
        return Auth::hasRole($this->roles);
    }
}
