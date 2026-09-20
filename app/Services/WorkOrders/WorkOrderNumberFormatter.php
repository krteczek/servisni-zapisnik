<?php
declare(strict_types=1);

namespace App\Services\WorkOrders;

use App\Core\Config;

class WorkOrderNumberFormatter
{
    /** @var array<int, string> */
    protected array $allowed = [];

    public function __construct() {
 
       $this->allowed = Config::get('workOrderSettings.allowed');
    }


    /**
     * @param string $format
     * @param array{prefix: string, number: string} $data
     * @return string
     */
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