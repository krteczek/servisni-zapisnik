<?php
declare(strict_types=1);

namespace App\Core;

use \Throwable;

/**
 * Kontejner pro data předávaná z controllerů do view šablon.
 * Poskytuje strukturovaný způsob předávání dat s výchozími hodnotami.
 *
 * Třída slouží jako DTO (Data Transfer Object) mezi controllery a views.
 * Všechny vlastnosti jsou public pro jednoduchý přístup z šablon.
 */
class ViewContext
{
    /**
     * @var string Titulek stránky pro <title> tag
     */
    public string $title = '';

    /**
     * @var array|null Data aktuálně přihlášeného uživatele
     */
    public ?array $user = null;

    /**
     * @var array|null Seznam uživatelů (např. pro administraci)
     */
    public ?array $users = null;

    /**
     * @var bool Stav přihlášení uživatele
     */
    public bool $isLogged = false;

    /**
     * @var string CSRF token pro formuláře
     */
    public string $csrf = '';

    /** 
     * @var array Navigační menu sestavené z rout
     */
    public array $menu = [];

    /** 
     * @var array Systémové zprávy (info, varování)
     */
    public array $messages = [];

    /** 
     * @var array Validační chyby formulářů [field => [errors]]
     */
    public array $errors = [];

    /** 
     * @var array Flash zprávy (jednorázové notifikace)
     */
    public array $flash = [];

    /** 
     * @var array Data pro předvyplnění formulářů
     */
    public array $data = [];

    /**
     * @var array Stará data formuláře po neúspěšném odeslání
     */
    public array $old = [];

    /**
     * @var array|null Definice globálních rolí
     */
    public ?array $roles = null;

    /**
     * @var string|null Výchozí globální role
     */
    public ?string $rolesDefault = null;

    /**
     * @var string|null Aktuálně vybraná role (pro role-switching)
     */
    public ?string $selectedRole = null;

    // TEAMS MODULE

    /**
     * @var array Seznam týmů
     */
    public array $teams = [];

    /**
     * @var array Data konkrétního týmu
     */
    public array $team = [];

    /**
     * @var array Členové týmu
     */
    public array $members = [];

    /**
     * @var array Uživatelé dostupní pro přidání do týmu
     */
    public array $availableUsers = [];

    /**
     * @var array Definice týmových rolí
     */
    public array $rolesInTeam = [];

    /**
     * @var array Týmy uživatelů (např. pro výběr)
     */
    public array $userTeams = [];

    // WORKORDER MODULE

    /**
     * @var array Seznam pracovních příkazů
     */
    public array $orders = [];

    /**
     * @var array Data konkrétního pracovního příkazu
     */
    public array $order = [];

    /**
     * @var array Data z POST požadavku (pro zpětné zobrazení)
     */
    public array $post = [];

    // ADMIN MODULE

    /**
     * @var array|null Logy/audit záznamy
     */
    public ?array $logs = [];

    /**
     * @var array|null Filtry pro logy
     */
    public ?array $filters = [];

    /**
     * @var array|null Seznam společností/tenantů
     */
    public ?array $companies = [];

    /**
     * @var bool|null Oprávnění uzavřít pracovní příkaz
     */
    public ?bool $canCloseOrder = false;

    public ?array $tasks = [];
    public ?array $task = [];
    public ?array $teamMembers = [];
    public ?array $reports = [];
    public ?array $oldData = [];

    public ?array $contacts = [];

    public ?array $company = [];


    public string $mode = '';

    public ?string $exception = '';

    public string $type = '';

    public array $statuses = [];

    public string $search = '';
    // TODO: [MAINTENANCE] Přidat __construct() pro nastavení výchozích hodnot
    // TODO: [TYPING] Zvážit použití typed properties s nullable pro všechny proměnné
}