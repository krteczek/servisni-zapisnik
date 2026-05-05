<?php
declare(strict_types=1);

$path = dirname(__DIR__, 3) . '/bo.database.php';

if (!file_exists($path)) {
    throw new RuntimeException('Missing config file: ' . $path);
}

return require $path;
