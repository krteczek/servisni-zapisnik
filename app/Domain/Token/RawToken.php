<?php
declare(strict_types=1);

namespace Domain\Token;

final class RawToken
{
    public function __construct(
        public readonly string $value
    ) {}
}