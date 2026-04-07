<?php
declare(strict_types=1);

namespace App\Services\Onboarding;

class WorkOnboardingService
{
    public function run(array $data, int $companyId): void
    {
        // ⚠️ tady už běží TenantContext!
        TenantContext::set($companyId);

        (new WorkOrderModel())->create([
            'title' => 'První zakázka',
        ]);

        (new TaskModel())->create([
            'title' => 'První úkol',
        ]);
    }
}