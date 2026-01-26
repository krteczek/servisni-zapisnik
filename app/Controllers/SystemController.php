<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CompanyModel;


final class SystemController extends Controller
{
   public function index(): string
    {
        $companies = (new CompanyModel())->findAll();
			$this->view->companies = $companies;
        return $this->render('system/index');
    }}
