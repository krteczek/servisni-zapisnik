<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Core\LoggerHolder;
use App\Core\Url;
use App\Core\ViewContext;
use App\Models\CompanyDetailsModel;
use App\Models\CompanyModel;
use App\Services\Ares\AjaxStatus;

/**
 * Správa údajů vlastní firmy.
 *
 * Firma je určena přihlášeným uživatelem přes Auth::companyId().
 * company_id se nepřebírá z GET ani POST dat.
 */
final class CompanyController extends Controller
{
    private CompanyModel $companyModel;
    private CompanyDetailsModel $detailsModel; 

    public function __construct(ViewContext $view)
    {
        parent::__construct($view);

        $this->companyModel = new CompanyModel();
        $this->detailsModel = new CompanyDetailsModel();
    }

    /**
     * Formulář pro vytvoření firemních údajů.
     *
     * @return string
     */
    public function create(): string
    {
        AjaxStatus::set();
        $companyId = Auth::companyId();

        if ($companyId === null) {
            return $this->forbidden();
        }

        $company = $this->companyModel->find($companyId);

        if ($company === null) {
            return $this->notFound();
        }

        $details = $this->detailsModel->findByCompanyId($companyId);

        if ($details !== null) {
            Flash::error('Firemní údaje již existují.');
            Url::redirect('/{tenant}/system/company/detail');
        }

        $this->view->title = 'Firemní údaje: Vytvořit';
        $this->view->data = [
            'company' => $company,
            'details' => [],
        ];

        return $this->render('company/create');
    }

    /**
     * Uložení nových firemních údajů.
     *
     * @return string
     */
    public function store(): string
    {
        $companyId = Auth::companyId();

        if ($companyId === null) {
            return $this->forbidden();
        }

        $this->checkCsrf();

        $data = $this->validate($_POST);
dd($_POST, $data);
        if ($this->hasErrors()) {
            $this->view->data = [
                'company' => $this->companyModel->find($companyId),
                'details' => $data,
            ];

            return $this->render('company/create');
        }

        // Ověření před INSERTem – company_details je 1:1.
        if ($this->detailsModel->findByCompanyId($companyId) !== null) {
            Flash::error('Firemní údaje již existují.');
            Url::redirect('/{tenant}/system/company/detail');
        }

        try {
            $this->detailsModel->createForCompany($companyId, $data);

            Flash::success('Firemní údaje byly úspěšně uloženy.');
            Url::redirect('/{tenant}/system/company/detail');
        } catch (\Throwable $e) {
            LoggerHolder::get()->error(
                'CompanyController.store: failed',
                [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                    'trace'   => $e->getTraceAsString(),
                ]
            );

            $this->addError(
                'global',
                'Firemní údaje se nepodařilo uložit.'
            );

            $this->view->data = [
                'company' => $this->companyModel->find($companyId),
                'details' => $data,
            ];

            return $this->render('company/create');
        }
    }

    /**
     * Zobrazení detailu firemních údajů.
     *
     * @return string
     */
    public function detail(): string
    {
        $companyId = Auth::companyId();

        if ($companyId === null) {
            return $this->forbidden();
        }

        $company = $this->companyModel->find($companyId);

        if ($company === null) {
            return $this->notFound();
        }

        $details = $this->detailsModel->findByCompanyId($companyId);

        if ($details === null) {
            Flash::error('Firemní údaje zatím nejsou vyplněny.');
            Url::redirect('/{tenant}/system/company/create');
        }

        $this->view->title = 'Firemní údaje: Detaily';

        $this->view->data = [
            'company' => $company,
            'details' => $details,
        ];

        return $this->render('company/detail');
    }

    /**
     * Formulář pro změnu firemních údajů.
     *
     * @return string
     */
    public function edit(): string
    {
        $companyId = Auth::companyId();

        if ($companyId === null) {
            return $this->forbidden();
        }

        $company = $this->companyModel->find($companyId);

        if ($company === null) {
            return $this->notFound();
        }

        $details = $this->detailsModel->findByCompanyId($companyId);

        if ($details === null) {
            Flash::error('Firemní údaje zatím nejsou vyplněny.');
            Url::redirect('/{tenant}/system/company/create');
        }

        $this->setSessionCheck('company_details', $companyId);

        $this->view->title = 'Firemní údaje: Změna';

        $this->view->data = [
            'company' => $company,
            'details' => $details,
        ];

        return $this->render('company/edit');
    }

    /**
     * Uložení změn firemních údajů.
     *
     * @return string
     */
    public function update(): string
    {
        $companyId = Auth::companyId();

        if ($companyId === null) {
            return $this->forbidden();
        }

        $this->confirmSessionCheck(
            'company_details',
            $companyId,
            '/{tenant}/system/company/detail'
        );

        $this->checkCsrf();

        $details = $this->detailsModel->findByCompanyId($companyId);

        if ($details === null) {
            Flash::error('Firemní údaje neexistují.');
            Url::redirect('/{tenant}/system/company/create');
        }

        $data = $this->validate($_POST);

        if ($this->hasErrors()) {
            $this->view->title = 'Firemní údaje: Změna';

            $this->view->data = [
                'company' => $this->companyModel->find($companyId),
                'details' => array_merge($details, $data),
            ];

            return $this->render('company/edit');
        }

        try {
            $ok = $this->detailsModel->updateForCompany(
                $companyId,
                $data
            );

            if ($ok === false) {
                $this->addError(
                    'global',
                    'Firemní údaje se nepodařilo změnit.'
                );

                $this->view->title = 'Firemní údaje: Změna';
                $this->view->data = [
                    'company' => $this->companyModel->find($companyId),
                    'details' => array_merge($details, $data),
                ];

                return $this->render('company/edit');
            }

            Flash::success('Firemní údaje byly úspěšně změněny.');
            Url::redirect('/{tenant}/system/company/detail');
        } catch (\Throwable $e) {
            LoggerHolder::get()->error(
                'CompanyController.update: failed',
                [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                    'trace'   => $e->getTraceAsString(),
                ]
            );

            $this->addError(
                'global',
                'Firemní údaje se nepodařilo změnit.'
            );

            $this->view->title = 'Firemní údaje: Změna';

            $this->view->data = [
                'company' => $this->companyModel->find($companyId),
                'details' => array_merge($details, $data),
            ];

            return $this->render('company/edit');
        }
    }

    /**
     * Validace údajů firemního detailu.
     *
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    private function validate(array $post): array
    {
        $data = [
            'official_name'        => trim((string)($post['official_name'] ?? '')),
            'dic'                  => $this->nullableString($post['dic'] ?? null),
            'street'               => $this->nullableString($post['street'] ?? null),
            'house_number'         => $this->nullableString($post['house_number'] ?? null),
            'orientation_number'   => $this->nullableString($post['orientation_number'] ?? null),
            'city_part'            => $this->nullableString($post['city_part'] ?? null),
            'city'                 => trim((string)($post['city'] ?? '')),
            'postal_code'          => trim((string)($post['postal_code'] ?? '')),
            'country_code'         => strtoupper(
                trim((string)($post['country_code'] ?? 'CZ'))
            ),
            'delivery_address_1'   => $this->nullableString($post['delivery_address_1'] ?? null),
            'delivery_address_2'   => $this->nullableString($post['delivery_address_2'] ?? null),
            'delivery_address_3'   => $this->nullableString($post['delivery_address_3'] ?? null),
            'legal_form_code'      => $this->nullableString($post['legal_form_code'] ?? null),
            'legal_form_ros_code'  => $this->nullableString($post['legal_form_ros_code'] ?? null),
        ];

        $this->required(
            'official_name',
            $data['official_name'],
            'Název firmy je povinný.'
        );

        $this->required(
            'city',
            $data['city'],
            'Obec je povinná.'
        );

        $this->required(
            'postal_code',
            $data['postal_code'],
            'PSČ je povinné.'
        );

        $this->maxLength(
            'official_name',
            $data['official_name'],
            255,
            'Název firmy'
        );

        $this->maxLength(
            'dic',
            $data['dic'],
            20,
            'DIČ'
        );

        $this->maxLength(
            'street',
            $data['street'],
            255,
            'Ulice'
        );

        $this->maxLength(
            'house_number',
            $data['house_number'],
            20,
            'Číslo domu'
        );

        $this->maxLength(
            'orientation_number',
            $data['orientation_number'],
            20,
            'Číslo orientační'
        );

        $this->maxLength(
            'city_part',
            $data['city_part'],
            255,
            'Část obce'
        );

        $this->maxLength(
            'city',
            $data['city'],
            255,
            'Obec'
        );

        $this->maxLength(
            'postal_code',
            $data['postal_code'],
            10,
            'PSČ'
        );

        $this->maxLength(
            'country_code',
            $data['country_code'],
            2,
            'Kód země'
        );

        $this->maxLength(
            'delivery_address_1',
            $data['delivery_address_1'],
            255,
            'Doručovací adresa 1'
        );

        $this->maxLength(
            'delivery_address_2',
            $data['delivery_address_2'],
            255,
            'Doručovací adresa 2'
        );

        $this->maxLength(
            'delivery_address_3',
            $data['delivery_address_3'],
            255,
            'Doručovací adresa 3'
        );

        $this->maxLength(
            'legal_form_code',
            $data['legal_form_code'],
            10,
            'Kód právní formy'
        );

        $this->maxLength(
            'legal_form_ros_code',
            $data['legal_form_ros_code'],
            10,
            'ROS kód právní formy'
        );

        return $data;
    }

    /**
     * @param mixed $value
     * @return string|null
     */
    private function nullableString(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}