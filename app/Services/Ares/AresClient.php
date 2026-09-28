<?php

declare(strict_types=1);

namespace App\Services\Ares;

use App\Validators\ContactValidator;
use JsonException;

final class AresClient
{
    private const BASE_URL =
        'https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/';

    public function __construct(
        private readonly AresCompanyMapper $mapper,
    ) {
    }

    public function findByIco(string $ico): AresResult
    {
        $ico = trim($ico);

        if (!ContactValidator::validateCzechIco($ico)) {
            return AresResult::invalidIco();
        }

        $ch = curl_init(self::BASE_URL . rawurlencode($ico));

        if ($ch === false) {
            return AresResult::aresError(
                'Nepodařilo se inicializovat komunikaci s ARES.'
            );
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);

            return AresResult::aresError(
                'Komunikace s ARES selhala: ' . $error
            );
        }

        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);


        if ($httpStatus === 404) {
            return AresResult::invalidIco();
        } 


        if ($httpStatus !== 200) {
            return AresResult::aresError(
                'ARES vrátil neočekávaný HTTP stav: ' . $httpStatus
            );
        }

        try {
            /** @var mixed $decoded */
            $decoded = json_decode(
                $response,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            return AresResult::aresError(
                'ARES vrátil neplatný JSON.'
            );
        }

        if (!is_array($decoded)) {
            return AresResult::aresError(
                'ARES vrátil neočekávaný formát odpovědi.'
            );
        }

        /** @var array<string, mixed> $decoded */
        $data = $this->mapper->map($decoded);

        if ($data === null) {
            return AresResult::aresError(
                'ARES vrátil data v neočekávaném formátu.'
            );
        }

        return AresResult::ok($data);
    }
}