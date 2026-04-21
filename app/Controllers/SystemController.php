<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;
use App\Models\CompanyModel;


final class SystemController extends Controller
{
   public function index(): string
    {
        $companies = (new CompanyModel())->findAll();
			$this->view->companies = $companies;
        return $this->render('system/index');
    }
    
    
    public function companyDetail(int $id): string
    {
        $companyModel = new CompanyModel();
        $userModel    = new UserModel();

        $company = $companyModel->find($id);

        if (!$company) {
            throw new \RuntimeException('Firma nenalezena');
        }

        // ROOT → globální přístup
        $users = (new UserModel())->findByCompanyIdGlobal($id);

        $this->view->company = $company;
        $this->view->users   = $users;

        return $this->render('system/company/detail');
    }    
    
}
