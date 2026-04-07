<?php
declare(strict_types=1);

namespace App\Services\Onboarding;

use App\Core\Transaction;
use App\Core\DatabaseScope;
use Throwable;

class OnboardingService
{
    public function run(array $data): void
    {
        // 1️⃣ ADMIN část (MUST SUCCEED)
        $adminResult = Transaction::run(function () use ($data) {
            return (new AdminOnboardingService())->run($data);
        }, 'admin');

        // obsahuje např. company_id, db_name apod.
        $companyId = $adminResult['company_id'];
        $dbName    = $adminResult['db_name'];

        // 2️⃣ WORK část (MAY FAIL)
        try {
            DatabaseScope::work($dbName, function () use ($data, $companyId) {

                Transaction::run(function () use ($data, $companyId) {
                    (new WorkOnboardingService())->run($data, $companyId);
                }, 'work');

            });

            // ✅ success
            $this->markOnboardingDone($companyId);

        } catch (Throwable $e) {

            // ❌ fail (ale admin část zůstává)
            $this->markOnboardingFailed($companyId, $e);

            // log
            logger()->error('Onboarding work failed', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}