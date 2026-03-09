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

    /** ativace uživatele default  expirace (dny) */
    private const DEFAULT_ACTIVATE_EXPIRATION_DAYS = 3;

    /** reset hesla default   expirace (dny) */
    private const DEFAULT_RESET_EXPIRATION_MINUTES = 15;

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
    string $type,
    ?string $ip = null,
    ?string $userAgent = null
): string {

    $this->model->invalidateForUser($userId, $type);

    $rawToken = bin2hex(random_bytes(self::TOKEN_BYTES));
    $hash     = hash('sha256', $rawToken);

    $expiresAt = match ($type) {
        self::TYPE_RESET_PASSWORD => (new DateTimeImmutable())->modify('+' . self::DEFAULT_RESET_EXPIRATION_MINUTES . ' minutes')->format('Y-m-d H:i:s'),
        self::TYPE_ACTIVATE       => (new DateTimeImmutable())->modify('+' . self::DEFAULT_ACTIVATE_EXPIRATION_DAYS . ' days')->format('Y-m-d H:i:s'),
        default => throw new RuntimeException('Neznámý typ tokenu'),
    };

    $this->model->createToken([
        'user_id'     => $userId,
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
        $hash = hash('sha256', trim($rawToken));

        $row = $this->model->findValidByHash($hash, $type);

        if (!$row) {
            return ['ok' => false];
            //throw new RuntimeException('Odkaz je neplatný nebo expirovaný.');
        }
        $row['ok'] = true;
        return $row;
    }

    /* ==========================================================
     * CONSUME (atomic)
     * ========================================================== */

public function consume(string $rawToken, string $type): array
{
    $this->model->begin();

    try {
        $hash = hash('sha256', trim($rawToken));

        $row = $this->model->findValidByHashForUpdate($hash, $type);

        if (!$row) {
            throw new RuntimeException('Token je neplatný nebo expirovaný.');
        }

        $updated = $this->model->markUsed((int)$row['id']);

        if (!$updated) {
            throw new RuntimeException('Token již byl použit.');
        }

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
