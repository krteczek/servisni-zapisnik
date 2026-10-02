<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Types;

/**
 * Dynamický builder pro navigační menu aplikace.
 * Generuje hierarchickou strukturu menu na základě rout a aktuální URL.
 * Automaticky řeší aktivní stav položek a kontrolu oprávnění uživatele.
 *
 * @phpstan-import-type MenuRouteRow from Types
 * @phpstan-import-type MenuSection from Types
 * @phpstan-import-type MenuItem from Types
 */
final class Menu
{
    // TODO: [PERFORMANCE] Přidat caching sestaveného menu na úrovni uživatele + role
    // TODO: [FEATURE] Přidat podporu pro ikony menu a badge (notifikace, počty)

    /**
     * Sestaví hierarchické navigační menu z definovaných rout.
     * Kontroluje oprávnění uživatele a označuje aktivní položky.
     *
     * Vedlejší efekty:
     * - Volá Auth::check() a Auth::hasRole() pro kontrolu oprávnění
     * - Generuje URL pomocí Url::to()
     *
     * TODO: [MAINTENANCE] Přidat možnost konfigurovat pořadí položek menu (weight/order)
     * TODO: [FEATURE] Přidat podporu pro víceúrovňové menu (hlavní → sekce → submenu → ...)
     *
     * @param array<int, MenuRouteRow> $routes Pole všech rout z konfigurace
     * @param string $currentPath Aktuální URL cesta pro detekci aktivní položky
     * @return array<string, MenuSection> Hierarchická struktura menu
     */
    public static function build(array $routes, string $currentPath): array
    {
        $menu = [];

        foreach ($routes as $route) {

            if (!self::isAllowed($route)) {
                continue;
            }

            $routeMenu = $route['menu'] ?? null;
            $routeSubmenu = $route['submenu'] ?? null;
            $routeSection = $route['section'] ?? null;

            if ($routeMenu === null && $routeSubmenu === null) {
                continue;
            }

            $routePath = Url::to($route['path']);

            $section = $routeSection
                ?? $routeMenu
                ?? $route['path'];

            /*
             * POST routy:
             *
             * POST routa může obsahovat menu/submenu metadata kvůli
             * aktivnímu stavu, ale nikdy nesmí vytvořit vlastní položku menu.
             *
             * Předpokládáme, že odpovídající GET routa položku vytvoří.
             */
            if (($route['method'] ?? 'GET') === 'POST') {

                if (!isset($menu[$section])) {
                    continue;
                }

                /*
                 * Aktivace hlavní sekce podle aktuální URL.
                 */
                if (str_starts_with($currentPath, $routePath)) {
                    $menu[$section]['active'] = true;
                }

                /*
                 * Pokud POST routa odpovídá konkrétnímu submenu,
                 * označíme existující submenu jako aktivní.
                 */
                if ($routeSubmenu !== null) {
                    foreach ($menu[$section]['items'] as $index => $item) {

                        if ($item['label'] !== $routeSubmenu) {
                            continue;
                        }

                        if ($currentPath === $routePath) {
                            $menu[$section]['items'][$index]['active'] = true;
                            $menu[$section]['active'] = true;
                        }
                    }
                }

                continue;
            }

            /* ===== HLAVNÍ MENU ===== */

            if ($routeMenu !== null) {

                if (!isset($menu[$section])) {
                    $menu[$section] = [
                        'label'  => $routeMenu,
                        'path'   => $routePath,
                        'method' => $route['method'],
                        'active' => false,
                        'items'  => [],
                    ];
                }

                /*
                 * Aktivní hlavní sekce.
                 */
                if (str_starts_with($currentPath, $routePath)) {
                    $menu[$section]['active'] = true;
                }
            }

            /* ===== SUBMENU ===== */

            if (
                $routeSubmenu !== null
                && $routeSection !== null
                && isset($menu[$routeSection])
            ) {
                $active = ($currentPath === $routePath);

                $menu[$routeSection]['items'][] = [
                    'label'  => $routeSubmenu,
                    'path'   => $routePath,
                    'active' => $active,
                ];

                /*
                 * Pokud je aktivní submenu, aktivuj i hlavní sekci.
                 */
                if ($active) {
                    $menu[$routeSection]['active'] = true;
                }
            }
        }

        return $menu;
    }

    /**
     * Ověří, zda má aktuální uživatel přístup k dané routě/menu položce.
     * Kontroluje autentizaci a globální role.
     *
     * TODO: [SECURITY] Přidat kontrolu na týmové role pro menu položky
     * TODO: [FEATURE] Přidat podporu pro dynamické podmínky (např. 'if' callback)
     *
     * @param MenuRouteRow $route Konfigurace routy z routes.php
     * @return bool TRUE pokud má uživatel přístup, jinak FALSE
     */
    private static function isAllowed(array $route): bool
    {
        $isLogged = Auth::check();
        $requiresAuth = $route['auth'] ?? false;

        // nepřihlášený → nevidí chráněné
        if (!$isLogged && $requiresAuth) {
            return false;
        }

        // přihlášený → nevidí public (login, register…)
        if ($isLogged && !$requiresAuth) {
            return false;
        }

        // role check jen pro přihlášené
        if (
            $isLogged
            && ($route['roles'] ?? []) !== []
            && !Auth::hasRole($route['roles'])
        ) {
            return false;
        }

        return true;
    }
}