<?php
declare(strict_types=1);

namespace App\Core;

class Menu
{
    public static function items(): array
    {
        $items = require __DIR__ . '/../Config/menu.php';
        $role = Auth::role();

        return array_values(array_filter($items, function ($item) use ($role) {
            return $role !== null && in_array($role, $item['roles'], true);
        }));
    }
}