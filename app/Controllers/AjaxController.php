<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\AjaxStatus;
use App\Core\Controller;
use App\Services\Ares\AresClient;
use App\Services\Ares\AresCompanyMapper;
use App\Validators\ContactValidator;
/**
 * Stavové kódy a jejich význam:
 * 200  → ARES data máme
 * 403  → nemáš platné povolení AJAX požadavku
 * 422  → IČO není platné / ARES ho nenašel
 * 503  → ARES je technicky nedostupný
 */

final class AjaxController extends Controller
{
    /**
     * Ověří IČO přes ARES a vrátí výsledek jako JSON.
     *
     * Postup:
     * 1. ověří AjaxStatus vSession,
     * 2. normalizuje IČO,
     * 3. ověří jeho platnost,
     * 4. zavolá ARES,
     * 5. vrátí výsledek AJAX požadavku.
     *
     * IČO je předáváno v URL, AjaxStatus ověří, že požadavek přišel z našeho 
     * systému a ověření zneplatní.
     *
     * @param string $ico IČO z URL.
     * @return string JSON odpověď.
     */
    public function aresIco(string $ico): string
    {
        $ico = substr($ico, 3);
        

        /** Ověříme oprávněnost Ajax požadavku: */
        if(AjaxStatus::consume() === false) {
            return $this->json([
                'ok' => false,
                'error' => 'ajax',
                'message' => 'Platnost formuláře vypršela. Načtěte stránku znovu a opakujte odeslání.',
            ], 403);
        }

        $ico = ContactValidator::normalizeCzechIco($ico);

        if (!ContactValidator::validateCzechIco($ico)) {
            return $this->json([
                'ok' => false,
                'error' => 'invalid_ico',
                'message' => 'IČO není platné.',
            ], 422);
        }


        $client = new AresClient( new AresCompanyMapper());
        
        $result = $client->findByIco($ico);

        if ($result->isInvalidIco()) {
            return $this->json([
                'ok' => false,
                'error' => 'invalid_ico',
                'message' => 'IČO není validní. Načtěte znovu stránku (klávesa F5 nebo kombinace CTRl + R), zadejte správné IČO.',
            ], 422);
        }

        if ($result->isAresError()) {
            return $this->json([
                'ok' => false,
                'error' => 'ares_error',
                'message' => 'Údaje se momentálně nepodařilo ověřit. Zkuste to později nebo je zadejte ručně.',
            ], 503);
        }

        return $this->json([
            'ok' => true,
            'ico' => $ico,
            'data' => $result->data,
        ]);
    }

    /**
     * Vytvoří JSON HTTP odpověď.
     *
     * @param array<string, mixed> $data
     */
    private function json(array $data, int $status = 200): string
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        return json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }
}