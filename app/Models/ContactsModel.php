<?php
declare(strict_types=1);

namespace App\Models;



class ContactsModel extends BaseModel
{
    protected string $table = 'contacts';
    protected string $connection = 'work';
    protected bool $tenantAware = true;
    protected string $tenantColumn = 'company_id';
    // můžeš nechat úplně prázdné – BaseModel to obslouží
}