<?php
declare(strict_types=1);

namespace App\Services\Guards;

use App\Core\Config;
use App\Models\AccessLogModel;
use InvalidArgumentException;

final class AccessRateLimiterService
{
    public function __construct(
        private AccessLogModel $accessLogModel = new AccessLogModel()
    ) {}

    /**
     * Ověří, zda byl překročen limit chybových požadavků.
     *
     * @param string $action Název limitu z accessRateLimits.php
     * @param int $type Typ události (403 nebo 404)
     * @param string $ip IP adresa klienta
     */
    public function tooManyAttempts(
        string $action,
        int $type,
        string $ip
    ): bool {
        if (!in_array($type, [403, 404], true)) {
            throw new InvalidArgumentException(
                'Access rate limiter supports only HTTP 403 and 404.'
            );
        }

        $config = Config::get('accessRateLimits.' . $action);

        if (
            !is_array($config)
            || !isset($config['time'], $config['rate'])
            || !is_int($config['time'])
            || !is_int($config['rate'])
            || $config['time'] < 1
            || $config['rate'] < 1
        ) {
            throw new InvalidArgumentException(
                'Invalid access rate limit configuration: ' . $action
            );
        }

        $count = $this->accessLogModel->countRecent(
            type: $type,
            ip: $ip,
            minutes: $config['time']
        );

        return $count >= $config['rate'];
    }
}
