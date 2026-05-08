<?php
declare(strict_types=1);

namespace App\Core;

use App\Controllers\ErrorController;
use Throwable;
use LogicException;
use App\Core\LoggerHolder;

/**
 * Router pro zpracování HTTP požadavků a směrování na příslušné controllery.
 * Zajišťuje routování, autentizaci, autorizaci, tenant izolaci a zpracování chyb.
 *
 * Vedlejší efekty:
 * - Mění Session (nastavuje last_page)
 * - Přepíná databázový kontext (prostřednictvím Controller konstruktoru)
 * - Loguje chyby do logovacího systému
 * - Nastavuje HTTP redirecty
 */
final class Router
{
    /**
     * @var array Seznam rout s kompilovanými regex patterny
     */
    private array $routes = [];
    
    /**
     * @var ViewContext Sdílený kontext pro předání dat do všech view
     */
    private ViewContext $view;

    /**
     * Inicializuje router, načte routy a připraví sdílený view kontext.
     *
     * Vedlejší efekty:
     * - Inicializuje view kontext s uživatelskými daty
     * - Kompiluje regex patterny pro všechny cesty
     * - Sestavuje menu na základě rout a aktuální URL
     *
     * TODO: [PERFORMANCE] Při vysokém počtu rout (>100) zvážit použití trie nebo jiné optimalizované struktury
     * TODO: [MAINTENANCE] Rozdělit konstruktor na menší metody (initRoutes, initView, initMenu)
     *
     * @param array $routes Konfigurace rout z config/routes.php
     */
    public function __construct(array $routes)
    {
        // shared view context
        $this->view = new ViewContext();
        //$this->view->isLogged = Auth::check();
        //$this->view->user     = Auth::user();
        $user = Auth::user();

        $this->view->user     = $user;
        $this->view->isLogged = $user !== null;

        foreach ($routes as $route) {
            $this->routes[] = [
                ...$route,
                'method' => strtoupper($route['method'] ?? 'GET'),
                'regex'  => $this->compilePath($route['path']),
            ];
        }

        // menu
        $this->view->menu = Menu::build(
            $routes,
            rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/'
        );
    }

    /**
     * Zpracuje HTTP požadavek a vrátí odpověď.
     * Projde všechny routy, ověří shodu, zkontroluje oprávnění a zavolá příslušný controller.
     *
     * Vedlejší efekty:
     * - Nastavuje Session last_page pro úspěšné routy
     * - Může provést redirect při chybě autentizace/tenant izolace
     * - Loguje výjimky do loggeru
     *
     * TODO: [SECURITY] Přidat rate limiting na úrovni routeru
     * TODO: [MONITORING] Přidat logování zpracovaných rout pro debugging
     *
     * @param string $uri Požadovaná URI (včetně query stringu)
     * @param string $method HTTP metoda (GET, POST, PUT, DELETE, ...)
     * @return string HTML odpověď nebo prázdný string pro akční routy (redirecty)
     */
    public function dispatch(string $uri, string $method): string
    {
        $path = rtrim(parse_url($uri, PHP_URL_PATH), '/') ?: '/';

        foreach ($this->routes as $route) {

            if ($route['method'] !== strtoupper($method)) {
                continue;
            }

            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }

            // auth
            if (($route['auth'] ?? false) && !Auth::user()) {
                Flash::info('Byli jste odhlášeni systémem(změna oprávnění nebo neplatná session).');
                Url::redirect('/login');
            }

            // roles
            if (!empty($route['roles']) && !Auth::hasGlobalRole($route['roles'])) {
            	 AccessLogger::log(AccessLogger::TYPE_404);
                return (new ErrorController($this->view))->forbidden();
            }

            // params
            $params = [];
            foreach ($matches as $k => $v) {
                if (is_string($k)) {
                    $params[$k] = $v;
                }
            }

            // TODO: [FEATURE] Při implementaci multi-tenant admina upravit podmínku - admin může přistupovat k různým tenantům
            // tenant guard – dokud tenant existuje
            if (isset($params['tenant']) && Auth::user()) {
                $current = Auth::tenantSlug();

                if ($params['tenant'] !== $current) {
                    // vezmeme PATH, ne current URL (hash, query atd.)
                    $cleanPath = preg_replace(
                        '#^/' . preg_quote($params['tenant'], '#') . '#',
                        '',
                        $path
                    );

                    Url::redirect('/' . $current . $cleanPath);
                }
            }

            // TENANT = kontext, ne argument
            if (isset($params['tenant'])) {
                unset($params['tenant']);
            } 
            
            $this->resolveTitle($route);
            
            try {
                $response = $this->call($route['action'], $params);

                if ($response !== null) {
                    // ------- tady by to mělo podle mne být ------
                    // TODO: [UX] Zvážit ukládání full URL včetně query parametrů pro zpětné přesměrování
                    //Session::set('last_page', $path);
                    self::setLastPage($method);
                    
                    return $response;
                }

                // akční route (redirect, toggle, POST…)
                return '';
                
            } catch (Throwable $e) {
                return $this->handleError(500, $e);
            }
        }
        AccessLogger::log(AccessLogger::TYPE_404);
        return (new ErrorController($this->view))->notFound();
    }

    /**
     * Zavolá metodu controlleru s předanými parametry.
     * Automaticky konvertuje číselné parametry na integer.
     *
     * Očekává:
     * - Controller třída existuje a má požadovanou metodu
     * - Počet parametrů odpovídá aritě metody
     *
     * @param array $action Pole obsahující [class, method]
     * @param array $params Parametry extrahované z URL
     * @return string|null Návratová hodnota controlleru (HTML nebo null pro redirect)
     */
    private function call(array $action, array $params): ?string
    {
        [$class, $method] = $action;
        $controller = new $class($this->view);

        $args = [];
        foreach ($params as $value) {
            $args[] = ctype_digit($value) ? (int)$value : $value;
        }

        return $controller->$method(...$args);
    }

    /**
     * Zpracuje výjimku a vrátí chybovou stránku.
     * Loguje detaily výjimky pro pozdější analýzu.
     *
     * Vedlejší efekty:
     * - Zapíše chybu do logovacího systému
     *
     * TODO: [OBSERVABILITY] Přidat zápis do Sentry/NewRelic při produkčním nasazení
     * TODO: [SECURITY] V produkci skrýt detaily výjimky (file, line) před koncovým uživatelem
     *
     * @param int $code HTTP status code
     * @param Throwable|null $e Výjimka k zalogování
     * @return string HTML chybové stránky
     */
    private function handleError(int $code, ?Throwable $e = null): string
    {
        if ($e !== null) {
            LoggerHolder::get()->error(
                'Router exception',
                [
                    'code'    => $code,
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]
            );
        }

        $view  = new ViewContext();
        $error = new ErrorController($view);

        return $error->renderError($code, $e);
    }

    /**
     * Nastaví titulek stránky na základě konfigurace routy.
     * Priorita: explicitní title > menu+submenu > menu > default.
     *
     * Vedlejší efekty:
     * - Mění `$this->view->title`
     *
     * @param array $route Konfigurace aktuální routy
     * @return void
     */
    private function resolveTitle(array $route): void
    {
        if (isset($route['title'])) {
            $this->view->title = $route['title'];
        } elseif (isset($route['menu'], $route['submenu'])) {
            $this->view->title = "{$route['menu']} > {$route['submenu']}";
        } elseif (isset($route['menu'])) {
            $this->view->title = $route['menu'];
        } else {
            $this->view->title = 'Aplikace';
        }
    }

    /**
     * Kompiluje cestu s parametry na regex pattern.
     * Podporuje syntaxi `{param}` a `{param:regex}`.
     *
     * @param string $path Cesta s parametry (např. `/user/{id:\d+}`)
     * @return string Regex pattern pro porovnání s URI
     */
    private function compilePath(string $path): string
    {
        $regex = preg_replace_callback(
            '#\{(\w+)(?::([^}]+))?\}#',
            fn($m) => '(?P<' . $m[1] . '>' . ($m[2] ?? '[^/]+') . ')',
            $path
        );

        return '#^' . $regex . '$#';
    }

    private static function setLastPage(string $method): void
    {
        if (strtoupper($method) === 'GET') {
            Session::set(
                'last_page',
                $_SERVER['REQUEST_URI']
            );
        }        
    }
}