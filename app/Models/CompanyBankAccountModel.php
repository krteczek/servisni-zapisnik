<?php
declare(strict_types=1);

namespace App\Models;

final class CompanyBankAccountModel extends BaseModel
{
    /**
     * Název tabulky bez prefixu.
     *
     * @var string
     */
    protected string $table = 'company_bank_accounts';

    /**
     * Připojení k admin databázi.
     *
     * @var string
     */
    protected string $connection = 'admin';

    /**
     * Model JE tenant-aware.
     *
     * @var bool
     */
    protected bool $tenantAware = true;

    /**
     * Vrátí všechny bankovní účty aktuální firmy.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forCompany(): array
    {
        return $this->fetchAll(
            "SELECT *
             FROM {$this->tableName}
             WHERE {$this->tenantColumn} = :company_id
             ORDER BY is_default DESC, active DESC, name ASC, id ASC",
            [
                'company_id' => $this->tenantId(),
            ]
        );
    }

    /**
     * Vrátí aktivní bankovní účty aktuální firmy.
     *
     * Výchozí účet je uveden jako první.
     *
     * @return array<int, array<string, mixed>>
     */
    public function activeForCompany(): array
    {
        return $this->fetchAll(
            "SELECT *
             FROM {$this->tableName}
             WHERE {$this->tenantColumn} = :company_id
               AND active = 1
             ORDER BY is_default DESC, name ASC, id ASC",
            [
                'company_id' => $this->tenantId(),
            ]
        );
    }

    /**
     * Vrátí výchozí aktivní účet aktuální firmy.
     *
     * @return array<string, mixed>|null
     */
    public function defaultForCompany(): ?array
    {
        return $this->fetchOne(
            "SELECT *
             FROM {$this->tableName}
             WHERE {$this->tenantColumn} = :company_id
               AND active = 1
               AND is_default = 1
             LIMIT 1",
            [
                'company_id' => $this->tenantId(),
            ]
        );
    }

    /**
     * Vrátí účet podle ID.
     *
     * Tenant izolaci zajišťuje BaseModel::find().
     *
     * @param int $accountId
     * @return array<string, mixed>|null
     */
    public function findById(int $accountId): ?array
    {
        return $this->find($accountId);
    }

    /**
     * Vytvoří bankovní účet aktuální firmy.
     *
     * company_id se doplní v BaseModel::create().
     *
     * @param array<string, mixed> $data
     * @return int
     */
    public function createForCompany(array $data): int
    {
        return $this->create($data);
    }

    /**
     * Aktualizuje bankovní účet aktuální firmy.
     *
     * Tenant izolaci zajišťuje BaseModel::update().
     *
     * @param int $accountId
     * @param array<string, mixed> $data
     * @return bool
     */
    public function updateForCompany(
        int $accountId,
        array $data
    ): bool {
        return $this->update($accountId, $data);
    }

    /**
     * Nastaví účet jako výchozí.
     *
     * Předpokládá se, že účet patří aktuální firmě.
     *
     * @param int $accountId
     * @return bool
     */
    public function setDefault(int $accountId): bool
    {
        $account = $this->find($accountId);

        if ($account === null) {
            return false;
        }

        if ((int)$account['active'] !== 1) {
            return false;
        }

        /*
         * Nejprve zrušíme default u všech ostatních účtů
         * aktuální firmy.
         */
        $this->clearDefault();

        return $this->update(
            $accountId,
            [
                'is_default' => 1,
            ]
        );
    }

    /**
     * Zruší výchozí účet aktuální firmy.
     *
     * @return bool
     */
    public function clearDefault(): bool
    {
        $accounts = $this->fetchAll(
            "SELECT id
             FROM {$this->tableName}
             WHERE {$this->tenantColumn} = :company_id
               AND is_default = 1",
            [
                'company_id' => $this->tenantId(),
            ]
        );

        $ok = true;

        foreach ($accounts as $account) {
            if (
                !$this->update(
                    (int)$account['id'],
                    [
                        'is_default' => 0,
                    ]
                )
            ) {
                $ok = false;
            }
        }

        return $ok;
    }

    /**
     * Deaktivuje bankovní účet.
     *
     * Deaktivovaný účet nemůže zůstat výchozím účtem.
     *
     * @param int $accountId
     * @return bool
     */
    public function deactivate(int $accountId): bool
    {
        $account = $this->find($accountId);

        if ($account === null) {
            return false;
        }

        return $this->update(
            $accountId,
            [
                'active'     => 0,
                'is_default' => 0,
            ]
        );
    }

    /**
     * Aktivuje bankovní účet.
     *
     * Aktivace účet automaticky nenastavuje jako výchozí.
     *
     * @param int $accountId
     * @return bool
     */
    public function activate(int $accountId): bool
    {
        $account = $this->find($accountId);

        if ($account === null) {
            return false;
        }

        return $this->update(
            $accountId,
            [
                'active' => 1,
            ]
        );
    }
}