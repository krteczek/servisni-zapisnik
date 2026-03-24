<?php
declare(strict_types=1);

namespace App\Services\Tokens;

enum TokenResult: string
{
    case VALID   = 'valid';
    case EXPIRED = 'expired';
    case USED    = 'used';
    case INVALID = 'invalid';

}
