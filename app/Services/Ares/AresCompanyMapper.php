<?php

declare(strict_types=1);

namespace App\Services\Ares;

final class AresCompanyMapper
{
    /**
     * @param array<string, mixed> $data
     */
    public function map(array $data): ?CompanyDetailsData
    {
        $officialName = $this->string($data['obchodniJmeno'] ?? null);

        /** @var array<string, mixed>|null $address */
        $address = is_array($data['sidlo'] ?? null)
            ? $data['sidlo']
            : null;

        if ($officialName === null || $address === null) {
            return null;
        }

        $city = $this->string($address['nazevObce'] ?? null);
        $postalCode = $this->string($address['psc'] ?? null);

        if ($city === null || $postalCode === null) {
            return null;
        }

        /** @var array<string, mixed> $delivery */
        $delivery = is_array($data['adresaDorucovaci'] ?? null)
            ? $data['adresaDorucovaci']
            : [];

        return new CompanyDetailsData(
            officialName: $officialName,
            dic: $this->string($data['dic'] ?? null),

            street: $this->string($address['nazevUlice'] ?? null),
            houseNumber: $this->string($address['cisloDomovni'] ?? null),
            orientationNumber: $this->string(
                $address['cisloOrientacni'] ?? null
            ),
            cityPart: $this->string(
                $address['nazevCastiObce'] ?? null
            ),
            city: $city,
            postalCode: $postalCode,
            countryCode: $this->string(
                $address['kodStatu'] ?? null
            ) ?? 'CZ',

            deliveryAddress1: $this->string(
                $delivery['radekAdresy1'] ?? null
            ),
            deliveryAddress2: $this->string(
                $delivery['radekAdresy2'] ?? null
            ),
            deliveryAddress3: $this->string(
                $delivery['radekAdresy3'] ?? null
            ),

            legalFormCode: $this->string(
                $data['pravniForma'] ?? null
            ),
            legalFormRosCode: $this->string(
                $data['pravniFormaRos'] ?? null
            ),

            foundedAt: $this->string(
                $data['datumVzniku'] ?? null
            ),
            aresUpdatedAt: $this->string(
                $data['datumAktualizace'] ?? null
            ),
        );
    }

    private function string(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}