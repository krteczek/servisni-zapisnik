<?php
declare(strict_types=1);

use App\Services\Guards\BanType;

/**********************************************************************
 * accessRateLimits.php
 * Nastavení počtu povolených pokusů v čase a doby blokace přístupu.
 **********************************************************************/

/** TODO: Zvážit správu těchto hodnot přes rozhraní root. */

return [
    BanType::BAD_LOGIN => [
        'time' => 15,
        'rate' => 5,
        'ban'  => 60,
    ],

    BanType::SCANNING_WARNING => [
        'time' => 15,
        'rate' => 5,
        'ban'  => 30,//60
    ],

    BanType::SCANNING_BAN => [
        'time' => 60,
        'rate' => 20,
        'ban'  => 30,//180,
    ],
];