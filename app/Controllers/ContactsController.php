<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\ViewContext;
use App\Core\Url;
use App\Core\Flash;
use App\Core\Auth;
use App\Models\ContactsModel;
use App\Core\Types;

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
        $this->view->data = [];
        //$this->view->errors = [];

        return $this->render('contacts/create');
    }

    public function storeContact(): string
    {
        $this->checkCsrf();
        $data = $this->validateContacts($_POST);


        if ($this->hasErrors()) {
            $this->view->data = $data;
            return $this->render('contacts/create');
        }

        $ok = $this->model->create($data);
        if ($ok <= 0)
        {
        	  $this->addError('global', 'Litujeme, zákazníka se nepodařilo uložit do systému. Zkuste to,  prosím, později.');
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

        $contact = $this->getContactOrRedirect($id);

        $this->view->data = $contact;
        return $this->render('contacts/create');
    }

    public function updateContact(int $id): string
    {
        $this->checkCsrf();
        $contact = $this->getContactOrRedirect($id);

        $data = $this->validateContacts($_POST);
        
        if ($this->hasErrors()) {
            $this->view->data = array_merge($contact, $data);
            return $this->render('contacts/create');
        }

        $ok = $this->model->update($id, $data);
        if(!$ok)
        {
                $this->addError('global', 'Litujeme, zákazníka se nepodařilo uložit do systému. Zkuste to,  prosím, později.');
                $this->view->data = array_merge($contact, $data);
                return $this->render('contacts/create');
            }

        Flash::success('Zákazník uložen');
        Url::redirect('/{tenant}/contacts/index/#main');
    }

/**
 * @param array<string, mixed> $post
 * @return array<string, mixed>
 */
private function validateContacts(array $post): array
{
    $data = [
        'company_name' => trim($post['company_name'] ?? ''),
        'ico'          => trim($post['ico'] ?? ''),
        'dic'          => trim($post['dic'] ?? ''),
        'street'       => trim($post['street'] ?? ''),
        'city'         => trim($post['city'] ?? ''),
        'zip'          => trim($post['zip'] ?? ''),
        'country'      => trim($post['country'] ?? 'CZ'),
        'email'        => trim($post['email'] ?? ''),
        'phone'        => trim($post['phone'] ?? ''),
    ];

    // 🔴 povinné pole
    if ($data['company_name'] === '') {
        $this->addError('company_name', 'Název zákazníka je povinný');
    }

    // 🔧 délky
    $this->maxLength('company_name', $data['company_name'], 255, 'Název');
    $this->maxLength('ico', $data['ico'], 20, 'IČO');
    $this->maxLength('dic', $data['dic'], 20, 'DIČ');
    $this->maxLength('street', $data['street'], 255, 'Ulice');
    $this->maxLength('city', $data['city'], 100, 'Město');
    $this->maxLength('zip', $data['zip'], 20, 'PSČ');
    $this->maxLength('country', $data['country'], 100, 'Stát');
    $this->maxLength('email', $data['email'], 255, 'Email');
    $this->maxLength('phone', $data['phone'], 50, 'Telefon');

    return $data;
}


}