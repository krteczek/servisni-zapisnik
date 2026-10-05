<?php
declare(strict_types=1);

namespace App\Services\Guards;

use App\Models\BanModel;
use App\Core\Config;
use App\Core\Auth;
use App\Core\Request;

class BanService
{
    public static function isBanned(): bool
    {
        return (new BanModel())->isBanned(self::fingerprint());
    }

    public static function ban(string $type): void
    {
        $config = Config::get('rateLimits.' . mb_strtoupper($type, 'UTF-8'));
        $userId = null;
        if(Auth::check())
        {
        	   $userId = Auth::id();
        }

        (new BanModel())->createBan([
            'type' => $type,
            'user_id' => $userId,
            'fingerprint' => self::fingerprint(),
            'banned_until' => date('Y-m-d H:i:s', time() + $config['ban'] * 60),
            'created_at' => date('Y-m-d H:i:s'),
            'ip' => Request::ip(),
            'user_agent' => Request::ua(),
        ]);
    }

	public static function fingerprint(): int
	{
		$ip = Request::ip();
		$ua = Request::ua();
	   return unpack('N', hash('xxh32', $ip . '|' . $ua, true))[1];
	}

    /** metoda volá odhlášení v Auth */
    public static function banLogout(string $type): void
    {
        self::ban($type);

        Auth::banLogout();
    }
}