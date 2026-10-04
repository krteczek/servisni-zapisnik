<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Flash;
use App\Core\Types;
use App\Core\Url;
use App\Core\ViewContext;
use App\Models\ContactsModel;
use App\Services\Ares\AresSession;
use App\Services\Ares\AjaxStatus;
use App\Validators\ContactValidator;

/**
 * @phpstan-import-type ContactRow from Types
 */
final class ContactsController extends Controller
{
    private ContactsModel $model;

    /**
     * Pole, která lze převzít z ARES.
     *
     * Klíč vlevo odpovídá názvu hodnoty v ARES datech,
     * klíč vpravo názvu sloupce v contacts.
     *
     * @var array<string, string>
     */
    private const ARES_FIELDS = [
        'officialName'      => 'official_name',
        'dic'               => 'dic',
        'street'            => 'street',
        'houseNumber'       => 'house_number',
        'orientationNumber' => 'orientation_number',
        'cityPart'          => 'city_part',
        'city'              => 'city',
        'postalCode'        => 'postal_code',
        'countryCode'       => 'country_code',
        'deliveryAddress1'  => 'delivery_address_1',
        'deliveryAddress2'  => 'delivery_address_2',
        'deliveryAddress3'  => 'delivery_address_3',
    ];

    public function __construct(ViewContext $view)
    {
        parent::__construct($view);
        $this->model = new ContactsModel();
    }

    public function index(): string
    {
        $this->view->contacts = $this->model->all();

        return $this->render('contacts/index');
    }

    public function createContact(): string
    {
        AjaxStatus::set();

        $this->view->contacts = $this->model->all();

        $this->view->data = [
            'country_code' => 'CZ',
        ];

        return $this->render('contacts/create');
    }

    public function storeContact(): string
    {
        $this->checkCsrf();

        $validator = new ContactValidator();

        $data = $validator->validate([
            'official_name'      => $_POST['official_name'] ?? '',
            'ico'                => $_POST['ico'] ?? '',
            'dic'                => $_POST['dic'] ?? '',
            'street'             => $_POST['street'] ?? '',
            'house_number'       => $_POST['house_number'] ?? '',
            'orientation_number' => $_POST['orientation_number'] ?? '',
            'city_part'          => $_POST['city_part'] ?? '',
            'city'               => $_POST['city'] ?? '',
            'postal_code'        => $_POST['postal_code'] ?? '',
            'country_code'       => $_POST['country_code'] ?? 'CZ',
            'delivery_address_1' => $_POST['delivery_address_1'] ?? '',
            'delivery_address_2' => $_POST['delivery_address_2'] ?? '',
            'delivery_address_3' => $_POST['delivery_address_3'] ?? '',
            'email'              => $_POST['email'] ?? '',
            'phone'              => $_POST['phone'] ?? '',
            'bank_account'       => $_POST['bank_account'] ?? '',
            'bank_code'          => $_POST['bank_code'] ?? '',
            'notes'              => $_POST['notes'] ?? '',
        ]);

        if (!$validator->isValid()) {
            $this->view->errors = $validator->getErrors();
            $this->view->data = $data;

            return $this->render('contacts/create');
        }

        $ok = $this->model->create($data);

        if ($ok <= 0) {
            $this->addError(
                'global',
                'Litujeme, zákazníka se nepodařilo uložit do systému. Zkuste to, prosím, později.'
            );

            $this->view->data = $data;

            return $this->render('contacts/create');
        }

        Flash::success('Zákazník uložen');

        Url::redirect('/{tenant}/contacts/' . $ok . '/detail/#main');
    }

    /**
     * @return ContactRow
     */
    private function getContactOrRedirect(int $id): array
    {
        $contact = $this->model->find($id);

        if ($contact === null) {
            Flash::error('Zákazník nenalezen');
            Url::redirect('/{tenant}/contacts/index/#main');
        }

        return $contact;
    }

    /**
     * Ruční editace existujícího zákazníka.
     */
    public function manualEdit(int $id): string
    {
        AjaxStatus::set();

        $this->setSessionCheck('manual_contact_id', $id);

        $contact = $this->getContactOrRedirect($id);

        $this->view->data = $contact;

        return $this->render('contacts/manualEdit');
    }

    /**
     * Uložení ruční editace zákazníka.
     */
    public function manualEditUpdate(int $id): string
    {
        $this->confirmSessionCheck(
            'manual_contact_id',
            $id,
            '/{tenant}/contacts/index/#main'
        );

        $this->checkCsrf();

        $contact = $this->getContactOrRedirect($id);

        $validator = new ContactValidator();

        $data = $validator->validate([
            'official_name'      => $_POST['official_name'] ?? '',
            'ico'                => $_POST['ico'] ?? '',
            'dic'                => $_POST['dic'] ?? '',
            'street'             => $_POST['street'] ?? '',
            'house_number'       => $_POST['house_number'] ?? '',
            'orientation_number' => $_POST['orientation_number'] ?? '',
            'city_part'          => $_POST['city_part'] ?? '',
            'city'               => $_POST['city'] ?? '',
            'postal_code'        => $_POST['postal_code'] ?? '',
            'country_code'       => $_POST['country_code'] ?? 'CZ',
            'delivery_address_1' => $_POST['delivery_address_1'] ?? '',
            'delivery_address_2' => $_POST['delivery_address_2'] ?? '',
            'delivery_address_3' => $_POST['delivery_address_3'] ?? '',
            'email'              => $_POST['email'] ?? '',
            'phone'              => $_POST['phone'] ?? '',
            'bank_account'       => $_POST['bank_account'] ?? '',
            'bank_code'          => $_POST['bank_code'] ?? '',
            'notes'              => $_POST['notes'] ?? '',
        ]);

        if (!$validator->isValid()) {
            $this->setSessionCheck('manual_contact_id', $id);

            $this->view->errors = $validator->getErrors();
            $this->view->data = array_merge($contact, $data);

            return $this->render('contacts/manualEdit');
        }

        $ok = $this->model->update($id, $data);

        if (!$ok) {
            $this->addError(
                'global',
                'Litujeme, zákazníka se nepodařilo uložit do systému. Zkuste to, prosím, později.'
            );

            $this->setSessionCheck('manual_contact_id', $id);
            $this->view->data = array_merge($contact, $data);

            return $this->render('contacts/manualEdit');
        }

        Flash::success('Zákazník uložen');

        Url::redirect('/{tenant}/contacts/' . $id . '/detail/#main');
    }

    /**
     * Editace zákazníka pomocí ARES.
     *
     * Stránka pouze zobrazí aktuální stav zákazníka.
     * Samotné načtení ARES dat probíhá přes AJAX.
     */
    public function aresEdit(int $id): string
    {
        AjaxStatus::set();

        $this->setSessionCheck('ares_contact_id', $id);

        $contact = $this->getContactOrRedirect($id);

        $ico = ContactValidator::normalizeCzechIco(
            (string) ($contact['ico'] ?? '')
        );

        if ($ico === '') {
            Flash::error(
                'Tento zákazník nemá vyplněné IČO, takže údaje z ARES nelze načíst.'
            );

            Url::redirect('/{tenant}/contacts/' . $id . '/detail/#main');
        }

        $this->view->data = $contact;

        return $this->render('contacts/aresEdit');
    }

    /**
     * Uložení vybraných údajů z ARES.
     */
    public function aresEditUpdate(int $id): string
    {
        $this->confirmSessionCheck(
            'ares_contact_id',
            $id,
            '/{tenant}/contacts/index/#main'
        );

        $this->checkCsrf();

        $contact = $this->getContactOrRedirect($id);

        $aresUpdate = $_POST['ares_update'] ?? [];

        if (!is_array($aresUpdate)) {
            $aresUpdate = [];
        }

        /*
         * Z formuláře přijímáme pouze názvy polí.
         * Samotné hodnoty musí vždy pocházet ze serverové ARES session.
         */
        $selectedFields = [];

        foreach (self::ARES_FIELDS as $aresField => $dbField) {
            if (isset($aresUpdate[$aresField])) {
                $selectedFields[$aresField] = $dbField;
            }
        }

        /*
         * Pokud uživatel nic nepřevzal, není co ukládat.
         */
        if ($selectedFields === []) {
            Flash::success('Nebyla vybrána žádná změna.');

            Url::redirect('/{tenant}/contacts/' . $id . '/detail/#main');
        }

        $ares = AresSession::get('contact', $id);

        if ($ares === null) {
            $this->addError(
                'global',
                'Údaje z ARES již nejsou k dispozici. Načtěte je z ARES znovu.'
            );

            $this->view->data = $contact;

            return $this->render('contacts/aresEdit');
        }

        /*
         * ARES data musí odpovídat aktuálnímu IČO zákazníka.
         */
        $currentIco = ContactValidator::normalizeCzechIco(
            (string) ($contact['ico'] ?? '')
        );

        if ($currentIco === '' || $currentIco !== $ares['ico']) {
            AresSession::forget('contact', $id);

            $this->addError(
                'global',
                'Údaje z ARES neodpovídají aktuálnímu IČO zákazníka. Načtěte údaje z ARES znovu.'
            );

            $this->view->data = $contact;

            return $this->render('contacts/aresEdit');
        }

        $aresData = $ares['data'];

        /*
         * Výsledná data stavíme z aktuálního kontaktu.
         * Přepisujeme pouze pole, která uživatel skutečně vybral.
         */
        $data = [
            'official_name'      => $contact['official_name'] ?? '',
            'ico'                => $contact['ico'] ?? '',
            'dic'                => $contact['dic'] ?? '',
            'street'             => $contact['street'] ?? '',
            'house_number'       => $contact['house_number'] ?? '',
            'orientation_number' => $contact['orientation_number'] ?? '',
            'city_part'          => $contact['city_part'] ?? '',
            'city'               => $contact['city'] ?? '',
            'postal_code'        => $contact['postal_code'] ?? '',
            'country_code'       => $contact['country_code'] ?? 'CZ',
            'delivery_address_1' => $contact['delivery_address_1'] ?? '',
            'delivery_address_2' => $contact['delivery_address_2'] ?? '',
            'delivery_address_3' => $contact['delivery_address_3'] ?? '',
            'email'              => $contact['email'] ?? '',
            'phone'              => $contact['phone'] ?? '',
            'bank_account'       => $contact['bank_account'] ?? '',
            'bank_code'          => $contact['bank_code'] ?? '',
            'notes'              => $contact['notes'] ?? '',
        ];

        foreach ($selectedFields as $aresField => $dbField) {
            if (
                !array_key_exists($aresField, $aresData)
                || !is_scalar($aresData[$aresField])
            ) {
                continue;
            }

            $data[$dbField] = (string) $aresData[$aresField];
        }

        /*
         * Validujeme až výsledná data.
         * Tím se validují i hodnoty převzaté z ARES.
         */
        $validator = new ContactValidator();
        $validatedData = $validator->validate($data);

        if (!$validator->isValid()) {
            $this->setSessionCheck('ares_contact_id', $id);

            $this->view->errors = $validator->getErrors();
            $this->view->data = $validatedData;

            return $this->render('contacts/ares-edit');
        }

        $ok = $this->model->update($id, $validatedData);

        if (!$ok) {
            $this->addError(
                'global',
                'Litujeme, zákazníka se nepodařilo uložit do systému. Zkuste to, prosím, později.'
            );

            $this->setSessionCheck('ares_contact_id', $id);
            $this->view->data = $validatedData;

            return $this->render('contacts/aresEdit');
        }

        /*
         * Po úspěšném uložení už ARES data nepotřebujeme.
         */
        AresSession::forget('contact', $id);

        Flash::success('Zákazník byl aktualizován podle ARES');

        Url::redirect('/{tenant}/contacts/' . $id . '/detail/#main');
    }

    public function detailContact(int $id): string
    {
        $contact = $this->getContactOrRedirect($id);

        $this->view->data = $contact;

        return $this->render('contacts/detail');
    }
}