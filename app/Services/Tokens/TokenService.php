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
        string $expires = '+115 minutes',
    ): string {

        //$ip = inet_pton($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
		  $ip = $ip ? inet_pton($ip) : null;
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

        if (!in_array($type, TokenType::all()))
        {
        	  throw new RuntimeException('Požadavek na neznámý typ tokenu: ' . $type);
        }

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

            $hash = self::hash($rawToken);

            $row = $this->model->findValidByHashForUpdate($hash, $type);

            if (!$row) {
                return [
                'ok' => false,
                'result' => TokenResult::EXPIRED
                ];
            }

            $updated = $this->model->markUsed((int)$row['id']);

            if (!$updated) {
                return [
                'ok' => false,
                'result' => TokenResult::USED
                ];

            }

				$row['ok'] = true;
				$row['result'] = TokenResult::VALID;
            return $row;

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