<?php
declare(strict_types=1);

namespace App\Models;

final class CompanyDetailsModel extends BaseModel
{
    protected string $table      = 'company_details';
    protected string $connection = 'admin';
    protected bool $tenantAware  = false;

    public function findByCompanyId(int $companyId): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->tableName}
             WHERE company_id = :company_id
             LIMIT 1",
            ['company_id' => $companyId]
        );
    }

    public function createForCompany(int $companyId, array $data): int
    {
        $data['company_id'] = $companyId;

        return $this->insertRaw($data);
    }

    public function updateForCompany(int $companyId, array $data): bool
    {
        if ($data === []) {
            return false;
        }

        return $this->updateWhere(
            'company_id',
            $companyId,
            $data
        );
    }
}