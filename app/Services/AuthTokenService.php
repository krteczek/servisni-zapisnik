<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\AuthTokenModel;
use DateTimeImmutable;
use RuntimeException;
use Throwable;

final class AuthTokenService
{
    public const TYPE_ACTIVATE        = 'activate';
    public const TYPE_RESET_PASSWORD  = 'reset_password';

    /** délka tokenu v bytech (hex = *2 znaků) */
    private const TOKEN_BYTES = 32;

    /** default expirace (dny) */
    private const DEFAULT_EXPIRATION_DAYS = 3;

    private AuthTokenModel $model;

    public function __construct(?AuthTokenModel $model = null)
    {
        $this->model = $model ?? new AuthTokenModel();
    }

    /* ==========================================================
     * CREATE (invalidate old + create new)
     * ========================================================== */

    public function create(
        int $userId,
        int $companyId,
        string $type,
        ?string $ip = null,
        ?string $userAgent = null,
        ?int $expirationDays = null
    ): string {
        // zrušíme staré tokeny stejného typu
        $this->model->invalidateForUser($userId, $companyId, $type);

        $rawToken = bin2hex(random_bytes(self::TOKEN_BYTES));
        $hash     = hash('sha256', $rawToken);

			$expiresAt = match ($type) {
			    self::TYPE_RESET_PASSWORD => (new DateTimeImmutable())->modify('+15 minutes')->format('Y-m-d H:i:s'),
			    self::TYPE_ACTIVATE       => (new DateTimeImmutable())->modify('+7 days')->format('Y-m-d H:i:s'),
			    default => throw new RuntimeException('Neznámý typ tokenu'),
			};
			
			$this->model->createToken([
			    'user_id'     => $userId,
			    'company_id'  => $companyId,
			    'token_hash'  => $hash,
			    'type'        => $type,
			    'expires_at'  => $expiresAt,
			    'ip_created'  => $ip,
			    'user_agent'  => $userAgent,
			]);

        return $rawToken;
    }

    /* ==========================================================
     * VALIDATE
     * ========================================================== */

    public function validate(string $rawToken, string $type): array
    {
        $hash = hash('sha256', $rawToken);

        $row = $this->model->findValidByHash($hash, $type);

        if (!$row) {
            throw new RuntimeException('Odkaz je neplatný nebo expirovaný.');
        }

        return $row;
    }

    /* ==========================================================
     * CONSUME (atomic)
     * ========================================================== */

    public function consume(string $rawToken, string $type): array
    {
        $this->model->begin();

        try {
            $row = $this->validate($rawToken, $type);
            $this->model->markUsed((int) $row['id']);

            $this->model->commit();
            return $row;

        } catch (Throwable $e) {
            $this->model->rollback();
            throw $e;
        }
    }

    /* ==========================================================
     * HOUSEKEEPING
     * ========================================================== */

    public function cleanup(): int
    {
        return $this->model->deleteExpired();
    }
}
