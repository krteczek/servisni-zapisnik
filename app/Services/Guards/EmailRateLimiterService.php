<?php
declare(strict_types=1);

namespace App\Services\Guards;

use App\Models\EmailRateLimitModel;
use App\Core\Request;
use App\Core\Config;


class EmailRateLimiterService
{

    public function __construct(
        private EmailRateLimitModel $rateLimitModel = new EmailRateLimitModel()
    ) {

    }

public function tooManyAttempts(
    string $action,
    string $tenant,
    string $email
): bool {
	
    $config = Config::get('emailRateLimits.' . $action);
    
    $ip = Request::ip();
    $ua = Request::ua();
    $fingerprint = BanService::fingerprint();

    $attempts = $this->rateLimitModel->countRecentAttempts(
        action:          $action,
        fingerprint:     $fingerprint,
        windowMinutes:   $config['time']
    );

    if ($attempts >= $config['rate']) {
    	// přidáme ban
    	BanService::ban(
    		type:    $action,
    		);
        return true;
    }

    $this->rateLimitModel->logAttempt($action, $tenant, $email, $ip, $ua, $fingerprint);

    return false;
}

}