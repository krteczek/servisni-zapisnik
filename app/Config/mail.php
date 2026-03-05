<?php
declare(strict_types=1);
use App\Core\Config;

$appEnv = Config::get('app.env');
if ($appEnv === 'dev') {
	return [
	    'host'       => '127.0.0.1',
	    'port'       => 1025,
	    'username'   => null,
	    'password'   => null,
	    'encryption' => false,
	    'from_email' => 'noreply@localhost.test',
	    'from_name'  => 'Servisní zápisník',
	    'smtpAuth'   => false,
	    
	
	];
}

return [
    'host'       => 'smtp.tvojedomena.cz',
    'port'       => 587,
    'username'   => 'noreply@tvojedomena.cz',
    'password'   => 'REAL_SECRET',
    'encryption' => 'tls',
    'from_email' => 'noreply@tvojedomena.cz',
    'from_name'  => 'Servisní zápisník',
];