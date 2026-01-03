<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;

class PermissionMiddleware
{
    public function __construct(
        private string $permission
    ) {}

    public function handle(): bool
    {
        return Auth::can($this->permission);
    }
}
