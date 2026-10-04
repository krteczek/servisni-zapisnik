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
use App\Models\CompanyBankAccountModel;

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
    private CompanyBankAccountModel $bankAccountModel;

    public function __construct(ViewContext $view)
    {
        parent::__construct($view);

        $this->companyModel = new CompanyModel();
        $this->detailsModel = new CompanyDetailsModel();
        $this->bankAccountModel = new CompanyBankAccountModel();
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

        $ares = AresSession::get('company', $companyId);

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

        if ($ares['ico'] !== ($company['ico'] ?? null)) {
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

        $aresData = $ares['data'];

        
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

            AresSession::forget('company', $companyId);

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

        $ares = AresSession::get('company', $companyId);

        if ($ares !== null) {
            if ($ares['ico'] !== ($company['ico'] ?? null)) {
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

            $aresData = $ares['data'];

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

            AresSession::forget('company', $companyId);

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

    public function listBankAccounts(): string
    {
        $model = new CompanyBankAccountModel();

        $accounts = $model->forCompany();
        $this->view->bankAccounts = $accounts;
        return $this->render('company/bankAccountsList');
    }

    /**
 * Formulář pro vytvoření bankovního účtu.
 *
 * @return string
 */
public function createBankAccount(): string
{
    $companyId = Auth::companyId();

    if ($companyId === null) {
        return $this->forbidden();
    }

    $company = $this->companyModel->find($companyId);

    if ($company === null) {
        return $this->notFound();
    }

    //$this->setSessionCheck('company_bank_account_create', $companyId);

    $this->view->title = 'Bankovní účet: Nový';

    $this->view->data = [
        'company' => $company,
        'account' => [],
    ];

    return $this->render('company/bankAccountCreate');
}

    public function storeBankAccount(): string
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

        $data = [
            'name' => trim((string)($_POST['name'] ?? '')),
            'account_prefix' => trim((string)($_POST['account_prefix'] ?? '')),
            'account_number' => trim((string)($_POST['account_number'] ?? '')),
            'bank_code' => trim((string)($_POST['bank_code'] ?? '')),
            'iban' => trim((string)($_POST['iban'] ?? '')),
            'bic' => trim((string)($_POST['bic'] ?? '')),
        ];

        $isDefault = isset($_POST['is_default'])
            && (string)$_POST['is_default'] === '1';

        if ($data['name'] === '') {
            $this->addError(
                'name',
                'Název účtu je povinný.'
            );
        } elseif (mb_strlen($data['name']) > 100) {
            $this->addError(
                'name',
                'Název účtu může mít nejvýše 100 znaků.'
            );
        }

        if (mb_strlen($data['account_prefix']) > 6) {
            $this->addError(
                'account_prefix',
                'Předčíslí účtu může mít nejvýše 6 znaků.'
            );
        }

        if ($data['account_number'] === '') {
            $this->addError(
                'account_number',
                'Číslo účtu je povinné.'
            );
        } elseif (mb_strlen($data['account_number']) > 20) {
            $this->addError(
                'account_number',
                'Číslo účtu může mít nejvýše 20 znaků.'
            );
        }
        if($data['bank_code'] === '') {
            $this->addError(
                'bank_code',
                'Kód banky je povinný.'
            );
        } elseif (!preg_match('/^\d{4}$/', $data['bank_code'])) {
            $this->addError(
                'bank_code',
                'Kód banky musí být čtyřmístné číslo.'
            );
        }

        if (mb_strlen($data['bank_code']) > 4) {
            $this->addError(
                'bank_code',
                'Kód banky může mít nejvýše 4 znaky.'
            );
        }

        if($data['bank_code'] !== '' && !preg_match('/^\d{4}$/', $data['bank_code'])) {
            $this->addError(
                'bank_code',
                'Kód banky musí být čtyřmístné číslo.'
            );
        }

        if (mb_strlen($data['iban']) > 34) {
            $this->addError(
                'iban',
                'IBAN může mít nejvýše 34 znaků.'
            );
        }

        if (mb_strlen($data['bic']) > 11) {
            $this->addError(
                'bic',
                'BIC může mít nejvýše 11 znaků.'
            );
        }

        if ($this->hasErrors()) {
            $this->view->title = 'Bankovní účet: Nový';

            $this->view->data = [
                'company' => $company,
                'account' => [
                    ...$data,
                    'is_default' => $isDefault ? 1 : 0,
                ],
            ];

            return $this->render('company/bankAccountCreate');
        }

        try {
            $accountId = $this->bankAccountModel->createForCompany([
                ...$data,
                'active' => 1,
                'is_default' => 0,
            ]);

            if ($isDefault) {
                if (!$this->bankAccountModel->setDefault($accountId)) {
                    LoggerHolder::get()->error(
                        'CompanyController.storeBankAccount: '
                        . 'failed to set default account.',
                        [
                            'company_id' => $companyId,
                            'account_id' => $accountId,
                        ]
                    );

                    Flash::error(
                        'Bankovní účet byl uložen, '
                        . 'ale nepodařilo se jej nastavit jako výchozí.'
                    );

                    return Url::redirect(
                        '/{tenant}/system/company/bank-accounts/list'
                    );
                }
            }

            Flash::success(
                'Bankovní účet byl úspěšně uložen.'
            );

            return Url::redirect(
                '/{tenant}/system/company/bank-accounts/list/#main'
            );

        } catch (\Throwable $e) {
            LoggerHolder::get()->error(
                'CompanyController.storeBankAccount: failed',
                [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'company_id' => $companyId,
                ]
            );

            $this->addError(
                'global',
                'Bankovní účet se nepodařilo uložit.'
            );

            $this->view->title = 'Bankovní účet: Nový';

            $this->view->data = [
                'company' => $company,
                'account' => [
                    ...$data,
                    'is_default' => $isDefault ? 1 : 0,
                ],
            ];

            return $this->render('company/bankAccountCreate');
        }
    }

    public function toggleActiveBankAccount(int $accountId): string
    {
        $companyId = Auth::companyId();

        if ($companyId === null) {
            return $this->forbidden();
        }

        $company = $this->companyModel->find($companyId);

        if ($company === null) {
            return $this->notFound();
        }

        $this->checkCsrf();

        $account = $this->bankAccountModel->findById($accountId);

        if ($account === null) {
            return $this->notFound();
        }

        $active = (int)$account['active'] === 1;

        if ($active) {
            $ok = $this->bankAccountModel->deactivate($accountId);
            $message = 'Bankovní účet byl deaktivován.';
        } else {
            $ok = $this->bankAccountModel->activate($accountId);
            $message = 'Bankovní účet byl aktivován.';
        }

        if (!$ok) {
            LoggerHolder::get()->error(
                'Nepodařilo se změnit stav bankovního účtu.',
                [
                    'company_id' => $companyId,
                    'account_id' => $accountId,
                    'active'     => $active,
                ]
            );

            Flash::error(
                'Stav bankovního účtu se nepodařilo změnit.'
            );

            return Url::redirect(
                '/{tenant}/system/company/bank-accounts/list/#main'
            );
        }

        Flash::success($message);

        return Url::redirect(
            '/{tenant}/system/company/bank-accounts/list/#main'
        );
    }

    public function setDefaultBankAccount(int $accountId): string
    {
        $companyId = Auth::companyId();

        if ($companyId === null) {
            return $this->forbidden();
        }

        $company = $this->companyModel->find($companyId);

        if ($company === null) {
            return $this->notFound();
        }

        $this->checkCsrf();

        $account = $this->bankAccountModel->findById($accountId);

        if ($account === null) {
            return $this->notFound();
        }

        if ((int)$account['active'] !== 1) {
            Flash::error(
                'Neaktivní bankovní účet nelze nastavit jako výchozí.'
            );

            return Url::redirect(
                '/{tenant}/system/company/bank-accounts/list/#main'
            );
        }

        if (!$this->bankAccountModel->setDefault($accountId)) {
            LoggerHolder::get()->error(
                'Nepodařilo se nastavit výchozí bankovní účet.',
                [
                    'company_id' => $companyId,
                    'account_id' => $accountId,
                ]
            );

            Flash::error(
                'Výchozí bankovní účet se nepodařilo nastavit. Zkuste akci opakovat.'
            );

            return Url::redirect(
                '/{tenant}/system/company/bank-accounts/list/#main'
            );
        }

        Flash::success(
            'Bankovní účet byl nastaven jako výchozí.'
        );

        return Url::redirect(
            '/{tenant}/system/company/bank-accounts/list/#main'
        );
    }    
}