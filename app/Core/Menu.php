<?php
declare(strict_types=1);

namespace App\Core;

final class Menu
{
    public static function build(array $routes, string $currentPath): array
    {
        $menu = [];

        foreach ($routes as $route) {

            if (!self::isAllowed($route)) {
                continue;
            }

            $routePath = Url::to($route['path']);

            /* ===== HLAVNÍ MENU ===== */
            if (!empty($route['menu'])) {

                $section = $route['section'] ?? $route['path'];

                $menu[$section] ??= [
                    'label'  => $route['menu'],
                    'path'   => $routePath,
                    'method' => $route['method'] ?? 'GET',
                    'active' => false,
                    'items'  => [],
                ];

                // aktivní sekce = URL začíná cestou sekce
                if (str_starts_with($currentPath, $routePath)) {
                    $menu[$section]['active'] = true;
                }
            }

            /* ===== SUBMENU ===== */
            if (!empty($route['submenu']) && !empty($route['section'])) {

                $active = $currentPath === $routePath;

                $menu[$route['section']]['items'][] = [
                    'label'  => $route['submenu'],
                    'path'   => $routePath,
                    'active' => $active,
                ];

                if ($active) {
                    $menu[$route['section']]['active'] = true;
                }
            }
        }

        return $menu;
    }

    private static function isAllowed(array $route): bool
    {
        if (($route['auth'] ?? false) && !Auth::check()) {
            return false;
        }

        if (!empty($route['roles']) && !Auth::hasRole($route['roles'])) {
            return false;
        }

        return true;
    }
}
