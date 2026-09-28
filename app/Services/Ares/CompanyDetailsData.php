<?php

declare(strict_types=1);

namespace App\Services\Ares;

final class CompanyDetailsData
{
    public function __construct(
        public readonly string $officialName,
        public readonly ?string $dic,

        public readonly ?string $street,
        public readonly ?string $houseNumber,
        public readonly ?string $orientationNumber,
        public readonly ?string $cityPart,
        public readonly string $city,
        public readonly string $postalCode,
        public readonly string $countryCode,

        public readonly ?string $deliveryAddress1,
        public readonly ?string $deliveryAddress2,
        public readonly ?string $deliveryAddress3,

        public readonly ?string $legalFormCode,
        public readonly ?string $legalFormRosCode,

        public readonly ?string $foundedAt,
        public readonly ?string $aresUpdatedAt,
    ) {
    }
}