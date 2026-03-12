<?php
declare(strict_types=1);

namespace App\Services\Tokens;

enum TokenResult
{
    case VALID;
    case EXPIRED;
    case USED;
    case INVALID;
}
