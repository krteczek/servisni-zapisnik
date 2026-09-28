<?php

declare(strict_types=1);

namespace App\Services\Ares;

final class AresResult
{
    private function __construct(
        public readonly AresResultStatus $status,
        public readonly ?CompanyDetailsData $data,
        public readonly ?string $error,
    ) {
    }

    public static function ok(CompanyDetailsData $data): self
    {
        return new self(
            status: AresResultStatus::OK,
            data: $data,
            error: null,
        );
    }

    public static function invalidIco(): self
    {
        return new self(
            status: AresResultStatus::INVALID_ICO,
            data: null,
            error: 'IČO není platné.',
        );
    }

    public static function aresError(string $error): self
    {
        return new self(
            status: AresResultStatus::ARES_ERROR,
            data: null,
            error: $error,
        );
    }

    public function isOk(): bool
    {
        return $this->status === AresResultStatus::OK;
    }

    public function isInvalidIco(): bool
    {
        return $this->status === AresResultStatus::INVALID_ICO;
    }

    public function isAresError(): bool
    {
        return $this->status === AresResultStatus::ARES_ERROR;
    }
}