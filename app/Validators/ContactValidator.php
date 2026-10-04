<?php

declare(strict_types=1);

namespace App\Validators;

final class ContactValidator extends Validator
{
    /**
     * Validuje a normalizuje údaje zákazníka.
     *
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    public function validate(array $data): array
    {
        $this->errors = [];

        $validated = [
            'official_name'      => trim((string) ($data['official_name'] ?? '')),
            'ico'                => trim((string) ($data['ico'] ?? '')),
            'dic'                => trim((string) ($data['dic'] ?? '')),
            'street'             => trim((string) ($data['street'] ?? '')),
            'house_number'       => trim((string) ($data['house_number'] ?? '')),
            'orientation_number' => trim((string) ($data['orientation_number'] ?? '')),
            'city_part'          => trim((string) ($data['city_part'] ?? '')),
            'city'               => trim((string) ($data['city'] ?? '')),
            'postal_code'        => str_replace(
                ' ',
                '',
                trim((string) ($data['postal_code'] ?? ''))
            ),
            'country_code'       => strtoupper(
                trim((string) ($data['country_code'] ?? 'CZ'))
            ),
            'delivery_address_1' => trim((string) ($data['delivery_address_1'] ?? '')),
            'delivery_address_2' => trim((string) ($data['delivery_address_2'] ?? '')),
            'delivery_address_3' => trim((string) ($data['delivery_address_3'] ?? '')),
            'email'              => trim((string) ($data['email'] ?? '')),
            'phone'              => trim((string) ($data['phone'] ?? '')),
            'bank_account'       => trim((string) ($data['bank_account'] ?? '')),
            'bank_code'          => trim((string) ($data['bank_code'] ?? '')),
            'notes'              => trim((string) ($data['notes'] ?? '')),
        ];

        $this->required(
            'official_name',
            $validated['official_name'],
            'Oficiální název je povinný'
        );

        $this->maxLength(
            'official_name',
            $validated['official_name'],
            255,
            'Název'
        );

        $this->maxLength('ico', $validated['ico'], 20, 'IČO');
        $this->maxLength('dic', $validated['dic'], 20, 'DIČ');
        $this->maxLength('street', $validated['street'], 255, 'Ulice');

        $this->maxLength(
            'house_number',
            $validated['house_number'],
            20,
            'Číslo domu'
        );

        $this->maxLength(
            'orientation_number',
            $validated['orientation_number'],
            20,
            'Číslo orientační'
        );

        $this->maxLength(
            'city_part',
            $validated['city_part'],
            255,
            'Část obce'
        );

        $this->maxLength(
            'city',
            $validated['city'],
            255,
            'Město'
        );

        $this->maxLength(
            'postal_code',
            $validated['postal_code'],
            10,
            'PSČ'
        );

        $this->maxLength(
            'country_code',
            $validated['country_code'],
            2,
            'Kód státu'
        );

        $this->maxLength(
            'delivery_address_1',
            $validated['delivery_address_1'],
            255,
            'Doručovací adresa'
        );

        $this->maxLength(
            'delivery_address_2',
            $validated['delivery_address_2'],
            255,
            'Doručovací adresa'
        );

        $this->maxLength(
            'delivery_address_3',
            $validated['delivery_address_3'],
            255,
            'Doručovací adresa'
        );

        $this->maxLength(
            'email',
            $validated['email'],
            255,
            'Email'
        );

        $this->maxLength(
            'phone',
            $validated['phone'],
            50,
            'Telefon'
        );

        $this->maxLength(
            'bank_account',
            $validated['bank_account'],
            50,
            'Číslo účtu'
        );

        $this->maxLength(
            'bank_code',
            $validated['bank_code'],
            10,
            'Kód banky'
        );

        if (
            $validated['country_code'] === 'CZ'
            && $validated['ico'] !== ''
        ) {
            $validated['ico'] = self::normalizeCzechIco($validated['ico']);

            if (!self::validateCzechIco($validated['ico'])) {
                $this->addError('ico', 'IČO není platné');
            }
        }

        if (
            $validated['country_code'] === 'CZ'
            && $validated['postal_code'] !== ''
            && !self::validateCzechZip($validated['postal_code'])
        ) {
            $this->addError('postal_code', 'PSČ není platné');
        }

        return $validated;
    }

    /**
     * Ověří platnost českého IČO.
     */
    public static function validateCzechIco(string $ico): bool
    {
        $ico = trim($ico);

        if (!preg_match('/^\d{8}$/', $ico)) {
            return false;
        }

        $sum = 0;

        for ($i = 0; $i < 7; $i++) {
            $sum += (int) $ico[$i] * (8 - $i);
        }

        $remainder = $sum % 11;

        $checkDigit = match ($remainder) {
            0 => 1,
            1 => 0,
            default => 11 - $remainder,
        };

        return $checkDigit === (int) $ico[7];
    }

    /**
     * Ověří platnost formátu českého PSČ.
     */
    public static function validateCzechZip(string $zip): bool
    {
        $zip = trim($zip);
        $zip = str_replace(' ', '', $zip);

        return preg_match('/^\d{5}$/', $zip) === 1;
    }

    /**
     * Doplní české IČO zleva nulami na osm znaků.
     */
    public static function normalizeCzechIco(string $ico): string
    {
        return str_pad($ico, 8, '0', STR_PAD_LEFT);
    }
}