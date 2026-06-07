<?php
declare(strict_types=1);

namespace App\Models;

use DateTime;
use App\Core\Auth;
use App\Core\Roles;

class InternalInvoiceModel extends BaseModel
{   
    protected bool $tenantAware = true;
    protected string $table = 'internal_invoices';
    /**
     * Připojení k admin databázi (centrální registr tenantů).
     *
     * @var string
     */
    protected string $connection = 'admin';


}