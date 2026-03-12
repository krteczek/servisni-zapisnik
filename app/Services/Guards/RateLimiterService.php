<?php
declare(strict_types=1);

namespace App\Services\Guards;

use App\Models\RateLimitModel;
use App\Core\Request;
use App\Core\Config;
use App\Services\Guards\RataLimitService;

class RateLimiterService
{
	private static $config = [];
    public function __construct(
        private rateLimitModel $rateLimitModel
    ) {

    }

public function tooManyAttempts(
    string $action,
    string $tenant,
    string $email
): bool {
    $config = Config::get('ratelimit.' . mb_strtoupper($action, 'UTF-8'));
    $ip = Request::ip();
    $ua = Request::ua();
    $hash = BanService::fingerprint($ip, $ua);

    $attempts = $this->rateLimitModel->countRecentAttempts(
        action:          $action,
        tenant:          $tenant,
        email:           $email,
        ip:              $ip,
        ua:              $ua,
        hash:            $hash,
        windowMinutes:   $config['time']
    );

    if ($attempts >= $config['rate']) {
    	// přidáme ban
    	BanService::ban(
    		type:    $action,
    		ip:      $ip,
    		ua:      $ua,
    		minutes: $config['ban']
    		);
        return true;
    }

    $this->rateLimitModel->logAttempt($action, $tenant, $email, $ip, $ua);

    return false;
}

}