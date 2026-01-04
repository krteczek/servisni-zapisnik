<?php
declare(strict_types=1);

namespace App\Core;

class Menu
{
    public static function fromRoutes(array $routes): array
    {
        $items = [];

        foreach ($routes as $route) {

            if (empty($route['menu'])) {
                continue;
            }

            if (($route['auth'] ?? false) && !Auth::check()) {
                continue;
            }

            if (!empty($route['roles']) && !Auth::hasRole($route['roles'])) {
                continue;
            }

            $items[] = [
                'label'  => $route['menu'],
                'path'   => url($route['path']),
                'method' => $route['method'] ?? 'GET',
            ];
        }

        return $items;
    }
}
