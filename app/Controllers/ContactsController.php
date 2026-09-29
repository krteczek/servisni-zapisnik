<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\Ares\AjaxStatus;
use App\Core\Controller;
use App\Core\Flash;
use App\Core\Types;
use App\Core\Url;
use App\Core\ViewContext;
use App\Models\ContactsModel;
use App\Validators\ContactValidator;

/**
 * @phpstan-import-type ContactRow from Types
 */
class ContactsController extends Controller
{
    private ContactsModel $model;

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
        ]);

        if (!$validator->isValid()) {
            $this->view->errors = $validator->getErrors();
            $this->view->data = $data;
            //$this->view->contacts = $this->model->all();
            return $this->render('contacts/create');
        }

        $ok = $this->model->create($data);


        if ($ok <= 0) {
            $this->addError(
                'global',
                'Litujeme, zákazníka se nepodařilo uložit do systému. Zkuste to, prosím, později.'
            );

            $this->view->data = $data;
            //$this->view->contacts = $this->model->all();
            return $this->render('contacts/create');
        }

        Flash::success('Zákazník uložen');
        Url::redirect('/{tenant}/contacts/detail/#main');
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

    public function editContact(int $id): string
    {
        AjaxStatus::set();
        $this->setSessionCheck('contact_id', $id);
        $contact = $this->getContactOrRedirect($id);

        $this->view->data = $contact;
        // $this->view->contacts = $this->model->all();

        return $this->render('contacts/create');
    }

    public function updateContact(int $id): string
    {
        $this->confirmSessionCheck('contact_id', $id,  '/{tenant}/contacts/list/#main');

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
        ]);

        if (!$validator->isValid()) {
            $this->setSessionCheck('contact_id', $id);
            $this->view->errors = $validator->getErrors();
            $this->view->data = array_merge($contact, $data);

            return $this->render('contacts/create');
        }

        $ok = $this->model->update($id, $data);

        if (!$ok) {
            $this->addError(
                'global',
                'Litujeme, zákazníka se nepodařilo uložit do systému. Zkuste to, prosím, později.'
            );

            $this->view->data = array_merge($contact, $data);
            $this->setSessionCheck('contact_id', $id);

            return $this->render('contacts/create');
        }

        Flash::success('Zákazník uložen');
        Url::redirect('/{tenant}/contacts/index/#main');
    }
}
