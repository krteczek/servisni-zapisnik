<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\RegistrationRequestModel;
use DateTimeImmutable;
use RuntimeException;

final class RegistrationTokenService
{
    private const TOKEN_BYTES = 32;
    private const EXPIRATION_MINUTES = 30;

    private RegistrationRequestModel $model;

    public function __construct(?RegistrationRequestModel $model = null)
    {
        $this->model = $model ?? new RegistrationRequestModel();
    }

    public function create(string $email, ?string $ip = null, ?string $ua = null): string
    {
        $rawToken = bin2hex(random_bytes(self::TOKEN_BYTES));
        $hash     = hash('sha256', $rawToken);

        $expiresAt = (new DateTimeImmutable())
            ->modify('+' . self::EXPIRATION_MINUTES . ' minutes')
            ->format('Y-m-d H:i:s');

        $this->model->upsert([
            'email'       => $email,
            'token_hash'  => $hash,
            'expires_at'  => $expiresAt,
            'ip_address'  => $ip,
            'user_agent'  => $ua,
        ]);

        return $rawToken;
    }

    public function consume(string $rawToken): array
    {
        $hash = hash('sha256', trim($rawToken));

        $row = $this->model->findValidByHash($hash);

        if (!$row) {
            throw new RuntimeException('Token neplatný nebo expirovaný.');
        }

        $this->model->deleteById((int)$row['id']);

        return $row;
    }
}