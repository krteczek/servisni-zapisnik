<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Types;

/**
 * Kontejner pro data předávaná z controllerů do view šablon.
 * Poskytuje strukturovaný způsob předávání dat s výchozími hodnotami.
 *
 * Třída slouží jako DTO (Data Transfer Object) mezi controllery a views.
 * Všechny vlastnosti jsou public pro jednoduchý přístup z šablon.
 *
 * @phpstan-import-type TaskListRow from Types
 * @phpstan-import-type TaskDetailRow from Types
 * @phpstan-import-type TaskBaseRow from Types
 * 
 * @phpstan-import-type WorkOrderRow from Types
 * @phpstan-import-type WorkOrderDetailRow from Types
 * @phpstan-import-type WorkOrderBaseRow from Types
 * 
 * @phpstan-import-type UserRow from Types
 * 
 * @phpstan-import-type TeamRow from Types 
 * @phpstan-import-type TeamMemberRow from Types 
 * @phpstan-import-type TeamDetailRow from Types
 * 
 * @phpstan-import-type MenuSection from Types
 * @phpstan-import-type MenuItem from Types
 * 
 * @phpstan-import-type ContactRow from Types
 * 
 * @phpstan-import-type ArchiveTaskRow from Types
 * @phpstan-import-type ArchiveOrderRow from Types
 * @phpstan-import-type ArchiveTaskListRow from Types
 * @phpstan-import-type WorkbenchDataRow from Types
 * 
 * 
 */
class ViewContext
{
    /**
     * @var string Titulek stránky pro <title> tag
     */
    public string $title = '';

    /**
     * @var UserRow|null Data aktuálně přihlášeného uživatele
     */
    public ?array $user = null;

    /**
     * @var array<int, UserRow>|null Seznam uživatelů (např. pro administraci)
     */
    public ?array $users = null;

    /**
     * @var bool Stav přihlášení uživatele
     */
    public bool $isLogged = false;

    /**
     * @var Router Router aktuální aplikace
     */
    public Router $router;

    /**
     * @var string CSRF token pro formuláře
     */
    public string $csrf = '';

    /**
    * @var array<string, MenuSection> Navigační menu sestavené z rout
    */
    public array $menu = [];

    /**
     * @var array<int, array<string, mixed>> Systémové zprávy (info, varování)
     */
    public array $messages = [];

    /**
     * @var array<string, list<string>> Validační chyby formulářů [field => [errors]]
     */
    public array $errors = [];

    /**
     * @var array<int, array<string, mixed>> Flash zprávy (jednorázové notifikace)
     */
    public array $flash = [];

    /**
     * @var array<string, mixed> Data pro předvyplnění formulářů
     */
    public array $data = [];

    /**
     * @var array<string, mixed> Stará data formuláře po neúspěšném odeslání
     */
    public array $old = [];

    /**
     * @var array<string, mixed>|null Definice globálních rolí
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
     * @var array<int, TeamRow> Seznam týmů
     */
    public array $teams = [];

    /** 
     * @var array<int, TeamDetailRow> Seznam týmů s členy (pro teams/index)
     */
    public array $teamsWithMembers = [];

    /**
     * @var TeamRow|null Data konkrétního týmu
     */
    public ?array $team = null;

    /**
     * @var array<int, TeamMemberRow> Členové týmu
     */
    public array $members = [];

    /**
     * @var array<int, UserRow> Uživatelé dostupní pro přidání do týmu
     */
    public array $availableUsers = [];

    /**
     * @var array<string, mixed> Definice týmových rolí
     */
    public array $rolesInTeam = [];

    /**
     * @var array<int, list<array{id: int, name: string, color: string}>> Týmy uživatelů (např. pro výběr)
     */
    public array $userTeams = [];

    // WORKORDER MODULE

    /**
     * @var array<int, WorkOrderRow> Seznam pracovních příkazů
     */
    public array $orders = [];

    /**
     * @var WorkOrderBaseRow|null Data konkrétního pracovního příkazu
     */
    public ?array $order = null;

    /**
     * @var WorkOrderDetailRow|null Detail pracovního příkazu (pro detail)
     */
    public ?array $orderDetail = null;

   /**
     * @var array<int, TaskDetailRow>|null Úkoly konkrétní zakázky (pro detail)
     */
    public ?array $orderTasks = null;

    /**
     * @var array<string, mixed> Data z POST požadavku (pro zpětné zobrazení)
     */
    public array $post = [];

     /**
     * @var WorkbenchDataRow
     */
    public array $WBData;

    // ADMIN MODULE

    /**
     * @var array<int, array<string, mixed>>|null Logy/audit záznamy
     */
    public ?array $logs = [];

    /** @var array<string, mixed>|null Jeden auditní záznam */
    public ?array $log = null;

    /**
     * @var array<string, mixed>|null Filtry pro logy
     */
    public ?array $filters = [];

    /**
     * @var array<int, array<string, mixed>>|null Seznam společností/tenantů
     */
    public ?array $companies = [];

    /**
     * @var bool|null Oprávnění uzavřít pracovní příkaz
     */
    public ?bool $canCloseOrder = false;

    /**
     * @var array<int, TaskListRow>|null Seznam úkolů (pro index)
     */
    public ?array $tasks = [];

    /**
     * @var TaskBaseRow|null Konkrétní úkol (pro detail/edit)
     */
    public ?array $task = null;

    /**
     * @var array<int, UserRow>|null Členové týmu (pro výběr)
     */
    public ?array $teamMembers = [];

    /**
     * @var array<int, array<string, mixed>>|null Reporty
     */
    public ?array $reports = [];

    /**
     * @var array<string, mixed>|null Stará data
     */
    public ?array $oldData = [];

    /** 
     * @var array<int, ContactRow>|null
     */
    public ?array $contacts = [];

    /**
     * @var array<string, mixed>|null Data společnosti
     */
    public ?array $company = [];

    /**
     * @var string Režim zobrazení
     */
    public string $mode = '';

    /**
     * @var string|null Výjimka (chybová zpráva)
     */
    public ?string $exception = '';

    /**
     * @var string Typ stránky
     */
    public string $type = '';

    /**
     * @var array<string, mixed> Statusy
     */
    public array $statuses = [];

    /**
     * @var string Vyhledávací řetězec
     */
    public string $search = '';


    // ARCHIVE MODULE

    /**
     * @var array<int, ArchiveTaskListRow>|null Seznam archivovaných úkolů
     */
    public ?array $archiveTasks = [];

    /**
     * @var ArchiveTaskRow|null Detail archivovaného úkolu
     */
    public ?array $archiveTask = null;

    /**
     * @var array<int, ArchiveOrderRow>|null Seznam archivovaných zakázek
     */
    public ?array $archiveOrders = [];

    /**
     * @var ArchiveOrderRow|null Detail archivované zakázky
     */
    public ?array $archiveOrder = null;
}