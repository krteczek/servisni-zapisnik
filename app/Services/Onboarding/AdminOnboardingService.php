<?php
declare(strict_types=1);

namespace App\Services\Onboarding;

use App\Models\CompanyModel;
use App\Models\UserModel;
use App\Models\TeamModel;

class AdminOnboardingService
{
    public function run(array $data): array
    {
    	$ok = (new CompanyRegistrationService()->completeAdmin(companyData: $data));
      return $ok;
    }
}