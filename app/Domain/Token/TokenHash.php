<?php
declare(strict_types=1);
namespace Domain\Token;

final class TokenHash
{
    public function __construct(
        public readonly string $value
    ) {}

    public static function fromRaw(RawToken $raw): self
    {
        return new self(hash('sha256', $raw->value));
    }
}