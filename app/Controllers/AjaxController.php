<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\Ares\AjaxStatus;
use App\Core\Controller;
use App\Services\Ares\AresClient;
use App\Services\Ares\AresCompanyMapper;
use App\Validators\ContactValidator;
use App\Models\ContactsModel;
use App\Models\TaskAssignmentModel;
use App\Core\Auth;
use App\Models\CompanyModel;

/**
 * Stavové kódy a jejich význam:
 * 200  → ARES data máme
 * 403  → nemáš platné povolení AJAX požadavku
 * 409  → konflikt, duplikátní ičo
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
     * 4. ověří, jestli existuje u nás v databázi kontaktů a pokud ano, vrátí chybu 409
     * 5. zavolá ARES,
     * 6. vrátí výsledek AJAX požadavku.
     *
     * IČO je předáváno v URL, AjaxStatus ověří, že požadavek přišel z našeho 
     * systému a ověření zneplatní.
     *
     * @param string $ico IČO z URL.
     * @return string JSON odpověď.
     */
    public function contactAresIco(string $ico): string
    {
        $ico = substr($ico, 3);
        

        /** Ověříme oprávněnost Ajax požadavku: */
        if(AjaxStatus::consume() === false) {
            return $this->json([
                'ok' => false,
                'error' => 'ajax',
                'message' => 'Platnost formuláře vypršela. Načtěte stránku znovu a opakujte odeslání.',
            ], 403);
        } /** */

        $ico = ContactValidator::normalizeCzechIco($ico);

        if (!ContactValidator::validateCzechIco($ico)) {
            return $this->json([
                'ok' => false,
                'error' => 'invalid_ico',
                'message' => 'IČO není platné.',
            ], 422);
        }

        /** provedeme ověření s naší db */
        $result = (new ContactsModel())->findByIco($ico);
        if($result !== null) {
            return $this->json([
                    'ok' => false,
                    'error' => 'duplicate_contact',
                    'message' => 'Ve vašem seznamu zákazníků již existuje záznam s tímto IČO. Vyberte ho ze seznamu výše.',
                    'ico' => null,
                    'data' => null,
                ], 409);
            
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

    public function getContactData(int $id): string {
        $data = (new ContactsModel())->find($id);
        if($data === null) {
            return $this->json([
                'ok' => false,
                'message' => 'Požadovaný záznam v databázi není...',
                'data' => [],
            ], 404);
        }

        return $this->json([
                'ok' => true,
                'message' => 'Data byla doplněna do polí formuláře...',
                'data' => $data,
            ], 200);
    }

    public function getTaskReports($taskId): string
    {
        $model = new TaskAssignmentModel();
        $data = $model->findByTask($taskId);
        if($data === []) {
             return $this->json([
                'ok' => false,
                'message' => 'Požadovaný záznam v databázi není...',
                'data' => [],
            ], 404);
        }

        foreach ($data as &$report) {
            $report['note'] = tx((string) $report['note']);
        }
        unset($report);

        return $this->json([
                'ok' => true,
                'message' => 'Reporty byly nahrány a zobrazeny...',
                'data' => $data,
        ], 200);
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

 public function companyAresIco(string $ico): string
{
    $ico = substr($ico, 3);
    
    if (AjaxStatus::consume() === false) {
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

    $companyId = Auth::companyId();

    if ($companyId === null) {
        return $this->json([
            'ok' => false,
            'error' => 'auth',
            'message' => 'Nelze určit aktuální firmu.',
        ], 403);
    }

    $companyModel = new CompanyModel();

    if ($companyModel->findOtherCompanyByIco($ico, $companyId) !== null) {
        return $this->json([
            'ok' => false,
            'error' => 'duplicate_company',
            'message' => 'Toto IČO již patří jiné firmě v systému.',
        ], 409);
    }

    $client = new AresClient(new AresCompanyMapper());
    $result = $client->findByIco($ico);

    if ($result->isInvalidIco()) {
        return $this->json([
            'ok' => false,
            'error' => 'invalid_ico',
            'message' => 'IČO není platné.',
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


}