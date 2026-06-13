<?php
declare(strict_types=1);

namespace App\Models;

final class RecurringTaskModel extends BaseModel
{
    protected string $table = 'recurring_tasks';
    protected string $connection = 'admin';
    protected bool $tenantAware = true;

}