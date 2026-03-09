<?php
declare(strict_types=1);

namespace App\Services;

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

    $this->rateLimitModel->logAttempt($action, $tenant, $email, $ip);

    $attempts = $this->rateLimitModel->countRecentAttempts(
        $action,
        $tenant,
        $email,
        $ip,
        $windowMinutes
    );

    return $attempts > $maxAttempts;
}}