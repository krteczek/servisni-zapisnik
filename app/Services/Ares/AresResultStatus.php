<?php

declare(strict_types=1);

namespace App\Services\Ares;

enum AresResultStatus: string
{
    case OK = 'ok';
    case INVALID_ICO = 'invalid_ico';
    case ARES_ERROR = 'ares_error';
}