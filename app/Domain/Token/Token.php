<?php
declare(strict_types=1);
namespace Domain\Token;

use DateTimeImmutable;

final class Token
{
    public function __construct(
        public readonly ?int $id,
        public readonly TokenType $type,
        public readonly string $email,
        public readonly ?int $userId,
        public readonly TokenHash $hash,
        public readonly DateTimeImmutable $expiresAt,
        public readonly ?DateTimeImmutable $usedAt,
        public readonly ?DateTimeImmutable $invalidatedAt,
        public readonly DateTimeImmutable $createdAt,
    ) {}

    public function isExpired(): bool
    {
        return $this->expiresAt < new DateTimeImmutable();
    }

    public function isUsed(): bool
    {
        return $this->usedAt !== null;
    }

    public function isInvalidated(): bool
    {
        return $this->invalidatedAt !== null;
    }

    public function isActive(): bool
    {
        return !$this->isExpired()
            && !$this->isUsed()
            && !$this->isInvalidated();
    }
}