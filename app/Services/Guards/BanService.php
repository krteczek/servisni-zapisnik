<?php
declare(strict_types=1);

namespace App\Services\Guards;

use App\Models\BanModel;
use App\Core\Request;
class BanService
{
    public static function isBanned(): bool
    {
        return (new BanModel())->isBanned(self::fingerprint());
    }

    public static function ban(string $type): void
    {
        $config = Config::get('ratelimit.' . mb_strtoupper($type, 'UTF-8'));
        (new BanModel())->createBan([
            'type' => $type,
            'fingerprint' => self::fingerprint(),
            'banned_until' => date('Y-m-d H:i:s', time() + $config['ban'] * 60),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

	public static function fingerprint(): int
	{
		$ip = Request::ip();
		$ua = Request::ua();
	   return unpack('N', hash('xxh32', $ip . '|' . $ua, true))[1];
	}

}