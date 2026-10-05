<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Url;
use App\Models\CompanyModel;
use App\Models\ContactsModel;
use App\Models\TaskAssignmentModel;
use App\Services\Ares\AjaxStatus;
use App\Services\Ares\AresClient;
use App\Services\Ares\AresCompanyMapper;
use App\Services\Ares\AresSession;
use App\Validators\ContactValidator;

/**
 * Stavové kódy a jejich význam:
 * 200  → ARES data máme
 * 403  → nemáš platné povolení AJAX požadavku
 * 409  → konflikt, duplikátní IČO
 * 422  → IČO není platné / ARES ho nenašel
 * 503  → ARES je technicky nedostupný
 */
final class AjaxController extends Controller
{
    /**
     * Ověří IČO kontaktu přes ARES a vrátí výsledek jako JSON.
     *
     * Nejprve ověří oprávnění AJAX požadavku, poté normalizuje
     * a validuje IČO. Následně ověří duplicitu kontaktu a pokud
     * kontakt neexistuje, načte údaje z ARES.
     *
     * Úspěšně zpracovaný požadavek nebo konflikt spotřebuje
     * jeden lístek AjaxStatus. Chyby vstupu a technická chyba
     * ARES lístek nespotřebují.
     *
     * @param string $ico IČO předané v URL včetně tříznakového prefixu.
     * @return string JSON odpověď.
     */
    public function contactAresIco(string $ico): string
    {
        $ico = substr($ico, 3);

        if (AjaxStatus::peek() === false) {
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

        $result = (new ContactsModel())->findByIco($ico);

        if ($result !== null) {
            AjaxStatus::consume();

            return $this->json([
                'ok' => false,
                'error' => 'duplicate_contact',
                'message' => 'Ve vašem seznamu zákazníků již existuje záznam s tímto IČO. Nelze přidat více zákazníků se stejným IČO. <a href="' .
                    Url::to('/{tenant}/contacts/list/#main') .
                    '">Zobrazit seznam zákazníků</a>.',
                'ico' => null,
                'data' => null,
            ], 409);
        }

        $client = new AresClient(new AresCompanyMapper());
        $result = $client->findByIco($ico);

        if ($result->isInvalidIco()) {
            return $this->json([
                'ok' => false,
                'error' => 'invalid_ico',
                'message' => 'IČO není validní. Načtěte znovu stránku (klávesa F5 nebo kombinace CTRL + R), zadejte správné IČO.',
            ], 422);
        }

        if ($result->isAresError()) {
            return $this->json([
                'ok' => false,
                'error' => 'ares_error',
                'message' => 'Údaje se momentálně nepodařilo ověřit. Zkuste to později nebo je zadejte ručně.',
            ], 503);
        }

        AjaxStatus::consume();

        return $this->json([
            'ok' => true,
            'ico' => $ico,
            'data' => $result->data,
        ]);
    }

    /**
     * Načte údaje kontaktu podle jeho ID a vrátí je jako JSON.
     *
     * @param int $id ID kontaktu.
     * @return string JSON odpověď.
     */
    public function getContactData(int $id): string
    {
        $data = (new ContactsModel())->find($id);

        if ($data === null) {
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
        ]);
    }

    /**
     * Načte reporty přiřazené k úkolu a vrátí je jako JSON.
     *
     * @param int $taskId ID úkolu.
     * @return string JSON odpověď.
     */
    public function getTaskReports(int $taskId): string
    {
        $model = new TaskAssignmentModel();
        $data = $model->findByTask($taskId);

        if ($data === []) {
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
        ]);
    }

    /**
     * Vytvoří JSON HTTP odpověď.
     *
     * @param array<string, mixed> $data Data JSON odpovědi.
     * @param int $status HTTP stavový kód odpovědi.
     * @return string JSON odpověď.
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

    /**
     * Ověří IČO aktuální firmy přes ARES a uloží načtená data
     * do serverové AresSession.
     *
     * Nejprve ověří oprávnění AJAX požadavku, poté normalizuje
     * a validuje IČO, ověří duplicitu jiné firmy a následně
     * načte údaje z ARES.
     *
     * Úspěšně zpracovaný požadavek nebo konflikt spotřebuje
     * jeden lístek AjaxStatus. Chyby vstupu a technická chyba
     * ARES lístek nespotřebují.
     *
     * @param string $ico IČO předané v URL včetně tříznakového prefixu.
     * @return string JSON odpověď.
     */
    public function companyAresIco(string $ico): string
    {
        $ico = substr($ico, 3);

        if (AjaxStatus::peek() === false) {
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
            AjaxStatus::consume();

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

        $data = (array) $result->data;

        AresSession::set(
            'company',
            $companyId,
            $ico,
            $data
        );

        AjaxStatus::consume();

        return $this->json([
            'ok' => true,
            'ico' => $ico,
            'data' => $data,
        ]);
    }

    /**
     * Ověří IČO existujícího kontaktu přes ARES a uloží načtená
     * data do serverové AresSession pro daný kontakt.
     *
     * Nejprve ověří oprávnění AJAX požadavku, poté normalizuje
     * a validuje IČO, ověří existenci kontaktu a případnou duplicitu.
     * Následně načte údaje z ARES.
     *
     * Úspěšně zpracovaný požadavek nebo konflikt spotřebuje
     * jeden lístek AjaxStatus. Chyby vstupu, neexistující kontakt
     * a technická chyba ARES lístek nespotřebují.
     *
     * @param int $id ID upravovaného kontaktu.
     * @param string $ico IČO předané v URL včetně tříznakového prefixu.
     * @return string JSON odpověď.
     */
    public function contactEditAresIco(int $id, string $ico): string
    {
        $ico = substr($ico, 3);

        if (AjaxStatus::peek() === false) {
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

        $model = new ContactsModel();

        $contact = $model->find($id);

        if ($contact === null) {
            return $this->json([
                'ok' => false,
                'error' => 'contact_not_found',
                'message' => 'Požadovaný zákazník nebyl nalezen.',
            ], 404);
        }

        $result = $model->findByIco($ico);

        if ($result !== null && $result['id'] !== $id) {
            AjaxStatus::consume();

            return $this->json([
                'ok' => false,
                'error' => 'duplicate_contact',
                'message' => 'Ve vašem seznamu zákazníků již existuje záznam s tímto IČO. Nelze přidat více zákazníků se stejným IČO. <a href="' .
                    Url::to('/{tenant}/contacts/list/#main') .
                    '">Zobrazit seznam zákazníků</a>.',
                'ico' => null,
                'data' => null,
            ], 409);
        }

        $client = new AresClient(new AresCompanyMapper());
        $aresResult = $client->findByIco($ico);

        if ($aresResult->isInvalidIco()) {
            return $this->json([
                'ok' => false,
                'error' => 'invalid_ico',
                'message' => 'IČO není platné.',
            ], 422);
        }

        if ($aresResult->isAresError()) {
            return $this->json([
                'ok' => false,
                'error' => 'ares_error',
                'message' => 'Údaje se momentálně nepodařilo ověřit. Zkuste to později nebo je zadejte ručně.',
            ], 503);
        }

        $data = (array) $aresResult->data;

        /*
         * ARES data držíme na serveru podle typu objektu a jeho ID.
         * Díky tomu mohou být současně otevřené např. kontakty 5, 8 a 12
         * a jejich ARES data se navzájem nepřepíšou.
         */
        AresSession::set(
            'contact',
            $id,
            $ico,
            $data
        );

        AjaxStatus::consume();

        return $this->json([
            'ok' => true,
            'ico' => $ico,
            'data' => $data,
        ]);
    }
}
