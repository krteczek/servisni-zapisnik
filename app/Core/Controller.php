<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $path, array $data = []): string
    {
        extract($data);
        ob_start();
        require __DIR__ . '/../Views/' . $path . '.php';
        return ob_get_clean();
    }
}