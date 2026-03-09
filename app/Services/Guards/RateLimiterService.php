<?php
declare(strict_types=1);

namespace App\Services\Guards;

use App\Models\EmailRateLimitModel;

class RateLimiterService
{
    public function __construct(
        private EmailRateLimitModel $rateLimitModel
    ) {}

public function tooManyAttempts(
    string $action,
    string $tenant,
    string $email,
    string $ip,
    int $maxAttempts,
    int $windowMinutes
): bool {

    $attempts = $this->rateLimitModel->countRecentAttempts(
        $action,
        $tenant,
        $email,
        $ip,
        $windowMinutes
    );

    if ($attempts >= $maxAttempts) {
        return true;
    }

    $this->rateLimitModel->logAttempt($action, $tenant, $email, $ip);

    return false;
}

}