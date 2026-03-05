<?php
declare(strict_types=1);

namespace App\Domain\Token;


/*
src/
  Domain/
    Token/
      Token.php
      TokenId.php
      TokenType.php
      RawToken.php
      TokenHash.php
      TokenRepository.php
      TokenService.php
      TokenUrlGenerator.php
*/
final class TokenService
{
        public function __construct(
        private TokenRepository $repository
    ) {}

    public function generate(
        TokenType $type,
        string $email,
        ?int $userId,
        DateInterval $ttl
    ): RawToken {

        // invalidujeme předchozí aktivní tokeny stejného typu
        $this->repository->invalidateActiveForEmail(
            $email,
            $type,
            'new_token_generated'
        );

        $raw = new RawToken(
            bin2hex(random_bytes(32))
        );

        $hash = TokenHash::fromRaw($raw);

        $now = new DateTimeImmutable();

        $token = new Token(
            id: null,
            type: $type,
            email: $email,
            userId: $userId,
            hash: $hash,
            expiresAt: $now->add($ttl),
            usedAt: null,
            invalidatedAt: null,
            createdAt: $now
        );

        $this->repository->save($token);

        return $raw;
    }

    public function validate(
        string $rawToken,
        TokenType $expectedType
    ): Token {

        $hash = TokenHash::fromRaw(
            new RawToken($rawToken)
        );

        $token = $this->repository->findByHash($hash);

        if (!$token) {
            throw new RuntimeException('Token not found.');
        }

        if ($token->type !== $expectedType) {
            throw new RuntimeException('Invalid token type.');
        }

        if (!$token->isActive()) {
            throw new RuntimeException('Token not active.');
        }

        return $token;
    }

    public function markAsUsed(Token $token): void
    {
        $this->repository->markAsUsed($token);
    }
   public function invalidateActiveForEmail(
        Email $email,
        TokenType $type
    ): void;

}