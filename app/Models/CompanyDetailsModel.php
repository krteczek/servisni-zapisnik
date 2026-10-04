<?php
declare(strict_types=1);

namespace App\Models;
use App\Core\Types;

/** @phpstan-import-type CompanyDetailsRow from Types */
final class CompanyDetailsModel extends BaseModel
{
    protected string $table      = 'company_details';
    protected string $connection = 'admin';
    protected bool $tenantAware  = false;

    /**
     * @return CompanyDetailsRow|null
     */
    public function findByCompanyId(int $companyId): ?array
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->tableName}
             WHERE company_id = :company_id
             LIMIT 1",
            ['company_id' => $companyId]
        );
    }
    
    /**
     * @param array<string, mixed> $data
     * @return int
     */
    public function createForCompany(int $companyId, array $data): int
    {
        $data['company_id'] = $companyId;

        return $this->insertRaw($data);
    }

    /**
     * @param array<string, mixed> $data
     * @return bool
     */
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