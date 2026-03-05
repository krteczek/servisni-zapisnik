<?php
declare(strict_types=1);
namespace Domain\Token;

interface TokenRepository
{
    public function save(Token $token): void;

    public function findByHash(TokenHash $hash): ?Token;

    public function markAsUsed(Token $token): void;

    public function invalidateActiveForEmail(
        string $email,
        TokenType $type,
        ?string $reason = null
    ): void;
}