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
        if (isset($_GET['search'])) {
            $this->view->companies = $this->searchCompany($_GET['search']);
            $this->view->search = (string) $_GET['search'];
        } else {
            $this->view->companies = (new CompanyModel())->findAll();
        }
        
        return $this->render('system/index');
    }
    
    
    public function companyDetail(int $id): string
    {
        $companyModel = new CompanyModel();
        $userModel    = new UserModel();

        $company = $companyModel->find($id);

        if ($company === null) {
            throw new \RuntimeException('Firma nenalezena');
        }

        // ROOT → globální přístup
        $users = (new UserModel())->findByCompanyIdGlobal($id);

        $this->view->company = $company;
        $this->view->users   = $users;

        return $this->render('system/detail');
    }    
    
    private function searchCompany(string $search): array
    {
        if ($search === '') {
            return [];
        }

        $companyModel = new CompanyModel();
        return $companyModel->searchCompany($search);
    }
}
