<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use PDO;

class UserModel extends BaseModel
{
    protected string $table = 'users';
    protected string $connection = 'admin';
    protected bool $tenantAware = true;
    protected string $tenantColumn = 'company_id';

    /* ==========================================================
     * BASIC
     * ========================================================== */

    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM {$this->tableName}
                WHERE email = :email
                AND {$this->tenantColumn} = :{$this->tenantColumn}
                LIMIT 1";

        return $this->fetchOne(
            $sql,
            $this->applyTenant(['email' => $email])
        );
    }

    /* ==========================================================
     * EXISTS
     * ========================================================== */

    public function emailExists(string $email, ?int $ignoreId = null): bool
    {
        $sql = "SELECT 1 FROM {$this->tableName}
                WHERE email = :email";

        $params = ['email' => $email];

        if ($ignoreId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $ignoreId;
        }

        $sql .= " AND {$this->tenantColumn} = :{$this->tenantColumn}
                  LIMIT 1";

        return $this->fetchOne(
            $sql,
            $this->applyTenant($params)
        ) !== null;
    }

    public function employeeNumberExists(string $employeeNumber, ?int $ignoreId = null): bool
    {
        $sql = "SELECT 1 FROM {$this->tableName}
                WHERE employee_number = :employee_number";

        $params = ['employee_number' => $employeeNumber];

        if ($ignoreId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $ignoreId;
        }

        $sql .= " AND {$this->tenantColumn} = :{$this->tenantColumn}
                  LIMIT 1";

        return $this->fetchOne(
            $sql,
            $this->applyTenant($params)
        ) !== null;
    }

    /* ==========================================================
     * PASSWORD
     * ========================================================== */

public function activateUser(int $id, string $hash): array
{
	     
    $sql = "
        UPDATE {$this->tableName}
        SET password_hash = :hash,
            active = 1
        WHERE id = :id
        LIMIT 1
    ";

    $stmt = $this->db()->prepare($sql);

     $ok = $stmt->execute([
        'hash' => $hash,
        'id'   => $id,
    ]);
		
		if (!$ok) {
			return ['ok' => false, 'result' => 'Uživatele se nepodařilo aktivovat'];
		}

		return ['ok' => $stmt->rowCount() === 1, 'result' => 'Uživatel byl aktivován.'];
		//return $ok;

}

    public function setPassword(int $id, string $hash): bool
    {
       return $this->update($id, [
            'password_hash' => $hash,
        ]);
    }


    /* tahle metoda je jen pro přihlášení uživatele, proto email i tenantid */
	public function findByEmailAndCompany(
	    string $email,
	    int $companyId
	): ?array {
	    $sql = "
	        SELECT *
	        FROM {$this->table}
	        WHERE email = :email
	          AND company_id = :company
	          AND active = 1
	        LIMIT 1
	    ";
	
	    $stmt = $this->db()->prepare($sql);
	    $stmt->execute([
	        'email'   => strtolower(trim($email)),
	        'company' => $companyId,
	    ]);
	
	    return $stmt->fetch() ?: null;
	}


    public function availableForTeam(int $teamId): array
    {
        $teamMembershipsTable = str_replace(
            $this->table,
            'team_memberships',
            $this->tableName
        );

        $sql = "
            SELECT u.*
            FROM {$this->tableName} u
            WHERE u.company_id = :company_id
              AND u.id NOT IN (
                  SELECT tm.user_id
                  FROM {$teamMembershipsTable} tm
                  WHERE tm.team_id = :team_id
                    AND tm.valid_to IS NULL
              )
            ORDER BY u.last_name, u.first_name
        ";

        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'team_id'    => $teamId,
            'company_id' => Auth::companyId(),
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

public function findByIdWithoutTenant(int $id): ?array
{
    $sql = "SELECT * FROM {$this->tableName}
            WHERE id = :id
            LIMIT 1";

    return $this->fetchOne($sql, ['id' => $id]);
}
}
