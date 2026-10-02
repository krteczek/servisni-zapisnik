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
use App\Services\Ares\AresSession;

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
            Flash::success(
                'Firemní údaje již existují. Můžete je upravit zde.'
            );

            Url::redirect('/{tenant}/system/company/edit');
        }

        $this->view->title = 'Firemní údaje: Vytvořit';

        $this->view->data = [
            'company' => $company,
            'details' => [],
        ];

        AjaxStatus::set();

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

        $company = $this->companyModel->find($companyId);

        if ($company === null) {
            return $this->notFound();
        }

        $data = $this->validate($_POST);

        if ($this->hasErrors() === true) {
            $this->view->title = 'Firemní údaje: Vytvořit';

            $this->view->data = [
                'company' => $company,
                'details' => $data,
            ];

            AjaxStatus::set();

            return $this->render('company/create');
        }

        $ares = AresSession::get();

        if ($ares === null) {
            $this->addError(
                'global',
                'Údaje z ARES nebyly načteny. Načtěte je, prosím, znovu.'
            );

            $this->view->title = 'Firemní údaje: Vytvořit';

            $this->view->data = [
                'company' => $company,
                'details' => $data,
            ];

            AjaxStatus::set();

            return $this->render('company/create');
        }

        if (($ares['ico'] ?? null) !== ($company['ico'] ?? null)) {
            $this->addError(
                'global',
                'Údaje z ARES neodpovídají IČO firmy. Načtěte je, prosím, znovu.'
            );

            $this->view->title = 'Firemní údaje: Vytvořit';

            $this->view->data = [
                'company' => $company,
                'details' => $data,
            ];

            AjaxStatus::set();

            return $this->render('company/create');
        }

        $aresData = $ares['data'] ?? null;

        if (!is_array($aresData)) {
            $this->addError(
                'global',
                'Údaje z ARES nejsou platné. Načtěte je, prosím, znovu.'
            );

            $this->view->title = 'Firemní údaje: Vytvořit';

            $this->view->data = [
                'company' => $company,
                'details' => $data,
            ];

            AjaxStatus::set();

            return $this->render('company/create');
        }

        $details = [
            'official_name'       => $aresData['officialName'] ?? '',
            'trade_name'          => $data['trade_name'],
            'dic'                 => $aresData['dic'] ?? null,
            'street'              => $aresData['street'] ?? null,
            'house_number'        => $aresData['houseNumber'] ?? null,
            'orientation_number'  => $aresData['orientationNumber'] ?? null,
            'city_part'           => $aresData['cityPart'] ?? null,
            'city'                => $aresData['city'] ?? '',
            'postal_code'         => $aresData['postalCode'] ?? '',
            'country_code'        => $aresData['countryCode'] ?? 'CZ',
            'delivery_address_1'  => $aresData['deliveryAddress1'] ?? null,
            'delivery_address_2'  => $aresData['deliveryAddress2'] ?? null,
            'delivery_address_3'  => $aresData['deliveryAddress3'] ?? null,
            'legal_form_code'     => $aresData['legalFormCode'] ?? null,
            'legal_form_ros_code' => $aresData['legalFormRosCode'] ?? null,
            'founded_at'          => $aresData['foundedAt'] ?? null,
            'ares_updated_at'     => $aresData['aresUpdatedAt'] ?? null,
        ];

        /*
         * Ověření před INSERTem kvůli běžnému uživatelskému toku.
         * 1:1 vztah navíc chrání PRIMARY KEY na company_id.
         */
        if ($this->detailsModel->findByCompanyId($companyId) !== null) {
            Flash::error('Firemní údaje již existují.');
            Url::redirect('/{tenant}/system/company/detail');
        }

        $detailId = null;

        try {
            $detailId = $this->detailsModel->createForCompany(
                $companyId,
                $details
            );

            AresSession::forget();

            Flash::success('Firemní údaje byly úspěšně uloženy.');
            Url::redirect('/{tenant}/system/company/detail');
        } catch (\Throwable $e) {
            LoggerHolder::get()->error(
                'CompanyController.store: failed',
                [
                    'message'   => $e->getMessage(),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                    'trace'     => $e->getTraceAsString(),
                    'detail_id' => $detailId,
                ]
            );

            $this->addError(
                'global',
                'Firemní údaje se nepodařilo uložit.'
            );

            $this->view->title = 'Firemní údaje: Vytvořit';

            $this->view->data = [
                'company' => $company,
                'details' => $data,
            ];

            AjaxStatus::set();

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

        AjaxStatus::set();

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

        $company = $this->companyModel->find($companyId);

        if ($company === null) {
            return $this->notFound();
        }

        $details = $this->detailsModel->findByCompanyId($companyId);

        if ($details === null) {
            Flash::error('Firemní údaje neexistují.');
            Url::redirect('/{tenant}/system/company/create');
        }

        $postData = $this->validate($_POST);

        if ($this->hasErrors()) {
            $this->view->title = 'Firemní údaje: Změna';

            $this->view->data = [
                'company' => $company,
                'details' => array_merge($details, $postData),
            ];

            AjaxStatus::set();

            return $this->render('company/edit');
        }

        /*
         * Základ tvoří aktuální údaje z databáze.
         * Z POSTu měníme pouze údaje, které formulář skutečně upravuje.
         */
        $data = [
            'official_name'       => $details['official_name'],
            'trade_name'          => $postData['trade_name'],
            'dic'                 => $details['dic'],
            'street'              => $details['street'],
            'house_number'        => $details['house_number'],
            'orientation_number' => $details['orientation_number'],
            'city_part'           => $details['city_part'],
            'city'                => $details['city'],
            'postal_code'         => $details['postal_code'],
            'country_code'        => $details['country_code'],
            'delivery_address_1'  => $details['delivery_address_1'],
            'delivery_address_2'  => $details['delivery_address_2'],
            'delivery_address_3'  => $details['delivery_address_3'],
            'legal_form_code'     => $details['legal_form_code'],
            'legal_form_ros_code' => $details['legal_form_ros_code'],
            'founded_at'          => $details['founded_at'],
            'ares_updated_at'     => $details['ares_updated_at'],
        ];

        $ares = AresSession::get();

        if ($ares !== null) {
            if (($ares['ico'] ?? null) !== ($company['ico'] ?? null)) {
                $this->addError(
                    'global',
                    'Údaje z ARES neodpovídají IČO firmy. Načtěte je, prosím, znovu.'
                );

                $this->view->title = 'Firemní údaje: Změna';

                $this->view->data = [
                    'company' => $company,
                    'details' => array_merge($details, $postData),
                ];

                AjaxStatus::set();

                return $this->render('company/edit');
            }

            $aresData = $ares['data'] ?? null;

            if (!is_array($aresData)) {
                $this->addError(
                    'global',
                    'Údaje z ARES nejsou platné. Načtěte je, prosím, znovu.'
                );

                $this->view->title = 'Firemní údaje: Změna';

                $this->view->data = [
                    'company' => $company,
                    'details' => array_merge($details, $postData),
                ];

                AjaxStatus::set();

                return $this->render('company/edit');
            }

            $aresFields = [
                'official_name'       => $aresData['officialName'] ?? null,
                'dic'                 => $aresData['dic'] ?? null,
                'street'              => $aresData['street'] ?? null,
                'house_number'        => $aresData['houseNumber'] ?? null,
                'orientation_number' => $aresData['orientationNumber'] ?? null,
                'city_part'           => $aresData['cityPart'] ?? null,
                'city'                => $aresData['city'] ?? null,
                'postal_code'         => $aresData['postalCode'] ?? null,
                'country_code'        => $aresData['countryCode'] ?? null,
                'delivery_address_1'  => $aresData['deliveryAddress1'] ?? null,
                'delivery_address_2'  => $aresData['deliveryAddress2'] ?? null,
                'delivery_address_3'  => $aresData['deliveryAddress3'] ?? null,
                'legal_form_code'     => $aresData['legalFormCode'] ?? null,
                'legal_form_ros_code' => $aresData['legalFormRosCode'] ?? null,
                'founded_at'          => $aresData['foundedAt'] ?? null,
                'ares_updated_at'     => $aresData['aresUpdatedAt'] ?? null,
            ];

            $aresFields = array_filter(
                $aresFields,
                static fn (mixed $value): bool => $value !== null
            );

            $data = array_replace($data, $aresFields);
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
                    'company' => $company,
                    'details' => array_merge($details, $postData),
                ];

                AjaxStatus::set();

                return $this->render('company/edit');
            }

            AresSession::forget();

            Flash::success(
                'Firemní údaje byly úspěšně změněny.'
            );

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
                'company' => $company,
                'details' => array_merge($details, $postData),
            ];

            AjaxStatus::set();

            return $this->render('company/edit');
        }
    }

    /**
     * Validace údajů z formuláře.
     *
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    private function validate(array $post): array
    {
        $data = [
            'trade_name' => $this->nullableString(
                $post['trade_name'] ?? null
            ),
        ];

        $this->maxLength(
            'trade_name',
            $data['trade_name'],
            255,
            'Obchodní název'
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