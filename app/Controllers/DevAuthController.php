<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Url;
use App\Models\CompanyModel;
use App\Core\Config;
use App\Models\UserModel;


class DevAuthController extends Controller
{
    public function devLoginIndex(): string
    {  
        $this->guardDevLogin();      
        return $this->render('auth/dev-login-index');
    }

    public function devLoginSet(string $role): void
    {
        $this->guardDevLogin();

        $companyId = (int) Config::get('app.company_id');
        $email = Config::get('app.dev_users.' . $role);

        // dd([$companyId, $email]);
        $companyModel = new CompanyModel();
        $company = $companyModel->find($companyId);

        $userModel = new UserModel();
        $user = $userModel->findByEmailAndCompany($email, $companyId);

        if ($user === null || $company === null) {
            throw new \Exception('User or company not found for dev login.');
        }

	    $toLogin = [
			'id'                => (int) $user['id'],
			'email'             => $user['email'],
			'global_role'       => $user['global_role'],
			'company_id'        => (int) $company['id'],
			'company_name'      => $company['name'],
			'tenant_slug'       => $company['slug'],
			'first_name'        => $user['first_name'] ?? null,
			'last_name'         => $user['last_name'] ?? null,
			'db_name'		    => $company['db_name'],
			'session_version'   => $user['session_version'],
		];

        Auth::login($toLogin);

        $url = match ($user['global_role']) {
            'root'  => '/' . $company['slug'] . '/root/list-companies/#main',
            'admin' => '/' . $company['slug'] . '/workbench/#main',
            'mistr' => '/' . $company['slug'] . '/workbench/#main',
            default => '/' . $company['slug'] . '/tasks/#main',
        };

        Url::redirect($url);
    }
    private function guardDevLogin(): void
    {
        if (Config::get('app.auth_bypass', false) !== true) {
            Url::redirect('/login');
        }

    }

}
