<?php
declare(strict_types=1);

namespace App\Services\Tokens;

use App\Models\TokenModel;
use DateTimeImmutable;
use RuntimeException;
use Throwable;

final class TokenService
{
    private const TOKEN_BYTES = 32;

    private TokenModel $model;

    public function __construct(?TokenModel $model = null)
    {
        $this->model = $model ?? new TokenModel();
    }


    public static function hash(string $rawToken): string
    {
    	return hash('sha256', trim($rawToken));
    }
    /* ==========================================================
     * CREATE
     * ========================================================== */

    public function create(
        string $type,
        string $email,
        ?int $userId = null,
        string $expires = '+15 minutes',
    ): string {

        //$ip = inet_pton($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
		  $ip = $ip ? inet_pton($ip) : null;
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

        $this->invalidate($email, $type);

        $rawToken = bin2hex(random_bytes(self::TOKEN_BYTES));
        $hash     = self::hash($rawToken);

        $expiresAt = (new DateTimeImmutable())
            ->modify($expires)
            ->format('Y-m-d H:i:s');

        $this->model->create([
            'type'        => $type,
            'email'       => $email,
            'user_id'     => $userId,
            'token_hash'  => $hash,
            'expires_at'  => $expiresAt,
            'ip_address'  => $ip,
            'user_agent'  => $ua,
        ]);

        return $rawToken;
    }

    /* ==========================================================
     * VALIDATE
     * ========================================================== */

    public function validate(string $rawToken, string $type): array
    {
        $hash = self::hash($rawToken);

        $row = $this->model->findValidByHash($hash, $type);

        if (!$row) {
            return ['ok' => false];
        }

        $row['ok'] = true;
        return $row;
    }

    /* ==========================================================
     * CONSUME
     * ========================================================== */

    public function consume(string $rawToken, string $type): array
    {
        //$this->model->begin();

        //try {

            $hash = self::hash($rawToken);

            $row = $this->model->findValidByHashForUpdate($hash, $type);

            if (!$row) {
                throw new RuntimeException('Token je neplatný nebo expirovaný.');
            }

            $updated = $this->model->markUsed((int)$row['id']);

            if (!$updated) {
                throw new RuntimeException('Token již byl použit.');
            }

            //$this->model->commit();

            return $row;

        //} catch (Throwable $e) {

         //   $this->model->rollback();
         //   throw $e;
        //}
    }

    /* ==========================================================
     * INVALIDATE
     * ========================================================== */

    public function invalidate(string $email, string $type): void
    {
        $this->model->invalidateActive($email, $type);
    }

    /* ==========================================================
     * CLEANUP
     * ========================================================== */

    public function cleanup(): int
    {
        return $this->model->deleteExpired();
    }
}