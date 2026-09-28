<?php
declare(strict_types=1);

namespace App\Validators;

final class ContactValidator extends Validator
{
    /**
     * Validuje a normalizuje kontaktní údaje.
     *
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    public function validate(array $data): array
    {
        $this->errors = [];

        $validated = [
            'company_name' => trim((string) ($data['company_name'] ?? '')),
            'ico'          => trim((string) ($data['ico'] ?? '')),
            'dic'          => trim((string) ($data['dic'] ?? '')),
            'street'       => trim((string) ($data['street'] ?? '')),
            'city'         => trim((string) ($data['city'] ?? '')),
            'zip'          => str_replace(' ', '', trim((string) ($data['zip'] ?? ''))),
            'country'      => strtoupper(trim((string) ($data['country'] ?? ''))),
            'email'        => trim((string) ($data['email'] ?? '')),
            'phone'        => trim((string) ($data['phone'] ?? '')),
        ];

        $this->required(
            'company_name',
            $validated['company_name'],
            'Název zákazníka je povinný'
        );

        $this->required(
            'street',
            $validated['street'],
            'Ulice je povinná'
        );

        $this->required(
            'city',
            $validated['city'],
            'Město je povinné'
        );

        $this->required(
            'zip',
            $validated['zip'],
            'PSČ je povinné'
        );

        $this->required(
            'country',
            $validated['country'],
            'Stát je povinný'
        );

        $this->maxLength(
            'company_name',
            $validated['company_name'],
            255,
            'Název'
        );

        $this->maxLength(
            'ico',
            $validated['ico'],
            20,
            'IČO'
        );

        $this->maxLength(
            'dic',
            $validated['dic'],
            20,
            'DIČ'
        );

        $this->maxLength(
            'street',
            $validated['street'],
            255,
            'Ulice'
        );

        $this->maxLength(
            'city',
            $validated['city'],
            100,
            'Město'
        );

        $this->maxLength(
            'zip',
            $validated['zip'],
            20,
            'PSČ'
        );

        $this->maxLength(
            'country',
            $validated['country'],
            100,
            'Stát'
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

        if (
            $validated['country'] === 'CZ'
            && $validated['ico'] !== ''            
        ) {
            $validated['ico'] = self::normalizeCzechIco($validated['ico']);
            if(self::validateCzechIco($validated['ico']) === false) {
                $this->addError('ico', 'IČO není platné');
            }
            
        }

        if (
            $validated['country'] === 'CZ'
            && $validated['zip'] !== ''
            && !self::validateCzechZip($validated['zip'])
        ) {
            $this->addError('zip', 'PSČ není platné');
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

    /* v případě,že vstupní string je kratší než 8 znaků, doplní na začátek nuly 
     * @property string $ico
     * @return string
     */ 
    public static function normalizeCzechIco(string $ico): string
    {
        return str_pad($ico, 8, '0', STR_PAD_LEFT);
    }
}