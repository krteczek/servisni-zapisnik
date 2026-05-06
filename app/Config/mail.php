<?php
declare(strict_types=1);

return [
    'host'       => 'mail.webglobe.cz',
    'port'       => 465,
    'username'   => 'info@krteczek.cz',
    'password'   => 'Mamamia123',
    'encryption' => 'ssl',
    'from_email' => 'noreply@krteczek.cz',
    'from_name'  => 'Bo systém',
];

/*
<?php
declare(strict_types=1);

$path = dirname(__DIR__, 3) . '/bo.mail.php';

if (!file_exists($path)) {
    throw new RuntimeException('Missing config file: ' . $path);
}

return require $path;
*/