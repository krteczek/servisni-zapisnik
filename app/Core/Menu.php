<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Dynamický builder pro navigační menu aplikace.
 * Generuje hierarchickou strukturu menu na základě rout a aktuální URL.
 * Automaticky řeší aktivní stav položek a kontrolu oprávnění uživatele.
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
     * @param array $routes Pole všech rout z konfigurace
     * @param string $currentPath Aktuální URL cesta pro detekci aktivní položky
     * @return array Hierarchická struktura menu ve formátu:
     *               [
     *                 'section' => [
     *                   'label' => 'Hlavní',
     *                   'path' => '/url',
     *                   'active' => true,
     *                   'items' => [['label' => 'Submenu', 'path' => '/sub', 'active' => false]]
     *                 ]
     *               ]
     */
    public static function build(array $routes, string $currentPath): array
    {
        $menu = [];

        foreach ($routes as $route) {

            if (!self::isAllowed($route)) {
                continue;
            }

            if (empty($route['menu']) && empty($route['submenu'])) {
                continue;
            }

            error_log('MENU: ' . json_encode([
    'path' => $route['path'],
    'menu' => $route['menu'] ?? null,
    'section' => $route['section'] ?? null,
    'url' => Url::to($route['path']),
], JSON_UNESCAPED_UNICODE));

            $routePath = Url::to($route['path']);
            $section   = $route['section'] ?? $route['menu'] ?? $route['path'];

            /* ===== HLAVNÍ MENU ===== */
            if (!empty($route['menu'])) {

                if (!isset($menu[$section])) {
                    $menu[$section] = [
                        'label'  => $route['menu'],
                        'path'   => $routePath,
                        'method' => $route['method'] ?? 'GET',
                        'active' => false,
                        'items'  => [],
                    ];
                }

                // TODO: [UX] Zvážit fuzzy matching pro aktivní stav (regex, wildcards)
                // aktivní sekce – URL začíná cestou sekce
                if (str_starts_with($currentPath, $routePath)) {
                    $menu[$section]['active'] = true;
                }
            }

            /* ===== SUBMENU ===== */
            if (
                !empty($route['submenu'])
                && !empty($route['section'])
                && isset($menu[$route['section']])
            ) {
                $active = ($currentPath === $routePath);

                $menu[$route['section']]['items'][] = [
                    'label'  => $route['submenu'],
                    'path'   => $routePath,
                    'active' => $active,
                ];

                // pokud je aktivní submenu, aktivuj i hlavní sekci
                if ($active) {
                    $menu[$route['section']]['active'] = true;
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
     * @param array $route Konfigurace routy z routes.php
     * @return bool TRUE pokud má uživatel přístup, jinak FALSE
     */
    private static function isAllowed(array $route): bool
    {
        $isLogged = Auth::check();
        $requiresAuth = $route['auth'] ?? false;

        // ❌ nepřihlášený → nevidí chráněné
        if (!$isLogged && $requiresAuth) {
            return false;
        }

        // ❌ přihlášený → nevidí public (login, register…)
        if ($isLogged && !$requiresAuth) {
            return false;
        }

        // role check jen pro přihlášené
        if ($isLogged && !empty($route['roles']) && !Auth::hasRole($route['roles'])) {
            return false;
        }

        return true;
    }
}