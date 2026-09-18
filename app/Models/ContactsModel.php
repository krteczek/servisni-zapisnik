<?php
declare(strict_types=1);

namespace App\Models;
use App\Core\Types;

/** 
 * @phpstan-import-type ContactRow from Types
 */
class ContactsModel extends BaseModel
{
    protected string $table = 'contacts';
    protected string $connection = 'admin';
    protected bool $tenantAware = true;
    protected string $tenantColumn = 'company_id';
    // můžeš nechat úplně prázdné – BaseModel to obslouží

    /**
     * @return ContactRow|null
     */
    public function find(int $id): ?array
    {
        return parent::find($id);
    }
}