<?php
declare(strict_types=1);
/*
return [
    'host'       => 'smtp.yourprovider.com',
    'port'       => 587,
    'username'   => 'noreply@yourdomain.com',
    'password'   => 'SMTP_PASSWORD',
    'encryption' => 'tls',
    'from_email' => 'noreply@yourdomain.com',
    'from_name'  => 'Your SaaS',
];
*/

if (APP_ENV === 'dev') {
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