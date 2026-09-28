<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\AjaxStatus;
use App\Core\Controller;
use App\Core\ViewContext;
use App\Core\Url;
use App\Core\Flash;
use App\Core\Auth;
use App\Models\ContactsModel;
use App\Core\Types;
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
        // debugViewVariables($this->view);

        return $this->render('contacts/index');
    }

    public function createContact(): string
    {
        AjaxStatus::set();
        dc($_SESSION);
        $this->view->data = ['country' => 'CZ'];
        //$this->view->errors = [];

        return $this->render('contacts/create');
    }

    public function storeContact(): string
    {
        $this->checkCsrf();

        $validator = new ContactValidator();

        $data = $validator->validate([
            'company_name' => $_POST['company_name'] ?? '',
            'ico'          => $_POST['ico'] ?? '',
            'dic'          => $_POST['dic'] ?? '',
            'street'       => $_POST['street'] ?? '',
            'city'         => $_POST['city'] ?? '',
            'zip'          => $_POST['zip'] ?? '',
            'country'      => $_POST['country'] ?? 'CZ',
            'email'        => $_POST['email'] ?? '',
            'phone'        => $_POST['phone'] ?? '',
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
        Url::redirect('/{tenant}/contacts/index/#main');
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
        //dc($_SESSION);

        $contact = $this->getContactOrRedirect($id);

        $this->view->data = $contact;
        return $this->render('contacts/create');
    }

public function updateContact(int $id): string
{
    
    $this->checkCsrf();

    $contact = $this->getContactOrRedirect($id);

    $validator = new ContactValidator();

    $data = $validator->validate([
        'company_name' => $_POST['company_name'] ?? '',
        'ico'          => $_POST['ico'] ?? '',
        'dic'          => $_POST['dic'] ?? '',
        'street'       => $_POST['street'] ?? '',
        'city'         => $_POST['city'] ?? '',
        'zip'          => $_POST['zip'] ?? '',
        'country'      => $_POST['country'] ?? 'CZ',
        'email'        => $_POST['email'] ?? '',
        'phone'        => $_POST['phone'] ?? '',
    ]);

    if (!$validator->isValid()) {
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

        return $this->render('contacts/create');
    }

    Flash::success('Zákazník uložen');
    Url::redirect('/{tenant}/contacts/index/#main');
}


}