<?php
declare(strict_types=1);

namespace App\Services\Guards;

use App\Models\BanModel;
use App\Core\Auth;
use App\Core\Request;

class BanService
{
    private BanModel $banModel;

    public function __construct(private int $durationMinutes = 60) 
    {
        $this->banModel = new BanModel();
    }

    public function ban(string $type): void
    {
        $userId = Auth::check() ? Auth::id() : null;

        $this->banModel->createBan([
            'type'          => $type,
            'user_id'       => $userId,
            'ip'            => Request::ip(),
            'user_agent'    => Request::ua(),
            'fingerprint'   => self::fingerprint(),
            'reason'        => BanType::description($type),
            'banned_until'  => date(
                                    'Y-m-d H:i:s',
                                    time() + $this->durationMinutes * 60
                                ),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function isBanned(): bool
    { 
        return $this->banModel->isBanned(self::fingerprint());
    }

    public function isAccountBanned(int $userId): bool
    {
        return $this->banModel->isAccountBanned($userId);
    }

    public static function fingerprint(): int
    {
        $ip = Request::ip();
        $ua = Request::ua();

        return (int) unpack('N', hash('xxh32', $ip . '|' . $ua, true))[1];
    }
 
    public function banLogout(string $type): void
    {
        $this->ban(type: $type);
        Auth::logout();
    }
}