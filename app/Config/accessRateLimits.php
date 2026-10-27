<?php
declare(strict_types=1);
/**********************************************************************
 *    accessRateLimits.php
 *  ---------------------------------------------
 *  nastavení počtu povolených pokusů v čase a doba blokace přístupu  *
 **********************************************************************/

/** možná by stálo za to, aby tohle mohl ovlivnovat root ve svém rozhraní... */
return [

    'BAD_LOGIN' => [
        'time' => 15,
        'rate' => 5,
        'ban'  => 60
    ],


	'SCANNING_WARNING' => [
	    'time' => 15,
	    'rate' => 5,
	    'ban'  => 60
	],

	'SCANNING_BAN' => [
	    'time' => 60,
	    'rate' => 20,
	    'ban'  => 180
	],
];