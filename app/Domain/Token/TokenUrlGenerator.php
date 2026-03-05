<?php
declare(strict_types=1);
namespace Domain\Token;

final class TokenUrlGenerator
{
    public function __construct(
        private string $baseUrl
    ) {}

    public function generate(
        TokenType $type,
        RawToken $raw
    ): string {

        return match ($type) {
            TokenType::CompanyRegistration =>
                $this->baseUrl . '/register/verify?token=' . $raw->value,

            TokenType::UserActivation =>
                $this->baseUrl . '/activate?token=' . $raw->value,

            TokenType::PasswordReset =>
                $this->baseUrl . '/password/reset?token=' . $raw->value,
        };
    }
}