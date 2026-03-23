<?php
declare(strict_types=1);

namespace App\Services\Guards;

use App\Models\RateLimitModel;
use App\Core\Request;
use App\Core\Config;


class RateLimiterService
{

    public function __construct(
        private RateLimitModel $rateLimitModel = new RateLimitModel()
    ) {

    }

public function tooManyAttempts(
    string $action,
    string $tenant,
    string $email
): bool {
	//var_dump($action);
    $config = Config::get('rateLimits.' . $action);
    //var_dump($config);exit;
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