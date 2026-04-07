<?php
declare(strict_types=1);

namespace App\Services\WorkOrders;

use App\Core\Config;

class WorkOrderNumberFormatter
{
     protected array $allowed = [];

     public function __construct() {
        $this->allowed = Config::get('workOrderSettings.allowed');
     }
     public function format(string $format, array $data): string
    {
        $return = str_replace(
            $this->allowed,
            [
                $data['prefix'],
                date('Y'),
                date('m'),
                date('d'),
                $data['number'],
            ],
            $format
        );
        return $return;
    }
}