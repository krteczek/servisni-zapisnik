<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Url;
use App\Core\Config;
use App\Core\Flash;
use App\Models\TeamModel;
use App\Models\TeamMembership;
use App\Models\UserModel;

final class TeamController extends Controller
{
		private const DEFAULT_COLOR = '#2196F3';
		private const MAX_LENGHT_COLOR_NAME = 100;

    // TODO: [SECURITY] Po implementaci ACL přidat kontrolu oprávnění pro všechny metody tohoto controlleru.
    // TODO: [PERFORMANCE] Při více než 50 týmech zvážit přidání stránkování do metod `index()` a `inactive()`.

    /* ==========================================================
     * VÝPIS AKTIVNÍ / NEAKTIVNÍ
     * ========================================================== */

    /**
     * Získá a zobrazí seznam týmů podle jejich aktivního stavu.
     * Načte členy každého týmu a spočítá jejich počet.
     *
     * @param bool $active TRUE pro aktivní týmy, FALSE pro neaktivní
     * @return string HTML výstup šablony teams/index
     */
    private function listByActive(bool $active): string
    {
        $teamModel       = new TeamModel();
        $membershipModel = new TeamMembership();

        $teams = $teamModel->byActive($active);

        foreach ($teams as $i => $team) {
            $members = $membershipModel->currentMembers($team['id']);
            $teams[$i]['members']       = $members;
            $teams[$i]['members_count'] = count($members);
            //$teams[$i]['members_teams_colors'] = 
         }
        $this->view->userTeams      = $membershipModel->activeTeamsByUsers();

        $this->view->teamsWithMembers = $teams;

        $this->view->mode = $active === true ? 'active' : 'inactive';
        return $this->render('teams/index');
    }

    /**
     * Zobrazí seznam aktivních týmů.
     * Veřejná wrapper metoda pro `listByActive(true)`.
     *
     * @return string HTML výstup šablony teams/index
     */
    public function index(): string
    {
        return $this->listByActive(true);
    }

    /**
     * Zobrazí seznam neaktivních týmů.
     * Veřejná wrapper metoda pro `listByActive(false)`.
     *
     * @return string HTML výstup šablony teams/index
     */
    public function inactive(): string
    {
        return $this->listByActive(false);
    }

    /* ==========================================================
     * VYTVOŘENÍ TÝMU
     * ========================================================== */

    /**
     * Zobrazí formulář pro vytvoření nového týmu.
     *
     * @return string HTML výstup šablony teams/create
     */
    public function create(): string
    {
        return $this->render('teams/create');
    }

    /**
     * Zpracuje POST požadavek na vytvoření nového týmu.
     * Validuje název týmu, vytvoří tým s výchozí barvou a přesměruje.
     *
     * Vedlejší efekty:
     * - Vytvoří nový záznam v tabulce týmů
     * - Nastaví flash zprávu
     * - Mění stav databáze
     *
     * @return string Pokud jsou chyby, vrátí HTML výstup šablony teams/create
     * @throws \Exception Pokud selže kontrola CSRF tokenu
     */
    public function store(): string
    {
        $this->checkCsrf();
        if($_POST === [])
        {
            Flash::error('Neplatná žádost. Musíte vyplnit požadovaná pole...');
            Url::back();
        }
        if($this->hasErrors() === true)
        {
            return $this->create();
        }
        $name  = trim($_POST['name'] ?? '');
        $color = trim($_POST['color'] ?? self::DEFAULT_COLOR);

        if ($name === '') {
            Flash::error('Název týmu je povinný.');
            Url::redirect('/{tenant}/teams/create/#main');
        }

        (new TeamModel())->create([
        'name'  => $name,
        'color' => $color,
    ]);
        Flash::success('Tým byl vytvořen.');
        Url::redirect('/{tenant}/teams/#main');
    }

    /* ==========================================================
     * EDITACE TÝMU
     * ========================================================== */

    /**
     * Zobrazí editační formulář pro konkrétní tým.
     * Načte tým, jeho členy, dostupné uživatele a konfiguraci rolí.
     *
     * Očekává:
     * - Platné ID existujícího týmu
     *
     * @param int $id ID týmu k editaci
     * @return string HTML výstup šablony teams/edit
     */
    public function edit(int $id): string
    {
        $this->setSessionCheck('team_id', $id);
        $teamModel       = new TeamModel();
        $membershipModel = new TeamMembership();
        $userModel       = new UserModel();

        $team = $teamModel->find($id);
        if ($team === null) {
            Flash::error('Tým neexistuje.');
            Url::redirect('/{tenant}/teams/#main');
        }

        $this->view->team           = $team;
        $this->view->members        = $membershipModel->currentMembers($id);
        $this->view->availableUsers = $userModel->availableForTeam($id);
        $this->view->rolesInTeam    = Config::get('roles_in_team')['roles'];
        $this->view->userTeams      = $membershipModel->activeTeamsByUsers();

        return $this->render('teams/edit');
    }

    /* ==========================================================
     * UPDATE – JEDNA AKCE NA JEDEN POST
     * ========================================================== */

    /**
     * Zpracuje všechny POST akce pro úpravu týmu (multiplexor).
     * Rozlišuje 4 typy operací podle přítomnosti POST parametrů.
     *
     * Vedlejší efekty:
     * - Mění data týmu (název, barva)
     * - Přidává/odebíra členy týmu
     * - Mění role členů
     * - Nastavuje flash zprávy
     * - Mění stav databáze
     *
     * Očekává:
     * - Platné CSRF token
     * - Existující ID týmu
     * - Konzistentní POST data pro danou operaci
     *
     * TODO: [REFACTOR] Při příští úpravě rozdělit tuto metodu na menší specializované metody (SRP).
     * TODO: [SECURITY] Přidat validaci, že uživatel má oprávnění měnit role (např. pouze admin může nastavit 'leader').
     *
     * @param int $id ID týmu k úpravě
     * @return string
     */
    public function update(int $id): string
    {
        $this->checkCsrf();
        if($this->hasErrors() === true)
        {
            return $this->edit($id);
        }

        $this->confirmSessionCheck('team_id', $id, '/{tenant}/teams/#main');

        $teamModel       = new TeamModel();
        $membershipModel = new TeamMembership();
        $userModel       = new UserModel();

        $team = $teamModel->find($id);

        if ($team === null) {
            Flash::error('Tým neexistuje.');
            Url::redirect('/{tenant}/teams/#main');
        }

        $roles     = Config::get('roles_in_team.roles');
        $default   = Config::get('roles_in_team.default');

        /* ===== ÚPRAVA NÁZVU / BARVY ===== */
        if (isset($_POST['name'], $_POST['color'])) {
            $name  = trim($_POST['name']);
            $color = trim($_POST['color']);

				if($name === '')
				{
					$this->addError('name', 'Název týmu je povinný.');
				}
				elseif(mb_strlen($name)>= self::MAX_LENGHT_COLOR_NAME)
				{
					$this->addError('name', 'Název týmu je příliš dlouhý.');
				}

				if($color === '')
				{
					$this->addError('color', 'Barva týmu je povinná.');
				}
				elseif(!self::isValidHexColor($color))
				{
					//poslali nějaký nesmysl, dáme defaultní barvu
					$color = self::DEFAULT_COLOR;
				}

				
            if ($name !== '' && $color !== '') {
                $teamModel->update($id, [
                    'name'  => $name,
                    'color' => $color,
                ]);
                Flash::success('Data týmu byla změněna.');
            } else {
                Flash::error('Data týmu se nepodařilo změnit');
            }

            Url::redirect('/{tenant}/teams/' . $id . '/edit/#main');
        }

        /* ===== PŘIDÁNÍ ČLENA ===== */
        if (isset($_POST['add_user_id'])) {
            $userId = (int) $_POST['add_user_id'];
            $role   = $_POST['role_in_team'] ?? $default;

            if (!isset($roles[$role])) {
                $role = $default;
            }

            $user = $userModel->find($userId);

            // root NIKDY
            if ($user === null || $user['global_role'] === 'root') {
            	Flash::error('Uživatele typu root nelze přidávat do týmů');
                Url::redirect('/{tenant}/teams/' . $id . '/edit/#changelist');
            }

            $ok = $membershipModel->add($userId, $id, $role);
            if((int) $ok > 0)
            {
               Flash::success('Uživatel byl přidán do týmu');
               Url::redirect('/{tenant}/teams/' . $id . '/edit/#changelist');
            }
         }

        /* ===== ZMĚNA ROLE ===== */
        //tyto role jsou jen přiznáním funkce členu skupiny, na aplikaci nemají vliv
        if (isset($_POST['change_user_role'], $_POST['role_in_team'])) {
            $membershipId = (int) $_POST['change_user_role'];
            $role         = $_POST['role_in_team'];

            // ošetřeni proti podvržení
            $roles = Config::get('roles_in_team.roles');
            if (!array_key_exists($role, $roles))
            {
                $role = Config::get('roles_in_team.default');
            }


            if (isset($roles[$role])) {
                $membershipModel->changeRole($membershipId, $role);
            }

            Url::redirect('/{tenant}/teams/' . $id . '/edit/#changelist');
        }

        /* ===== ODEBRÁNÍ ČLENA ===== */
        if (isset($_POST['remove_membership_id'])) {
            $membershipModel->end((int) $_POST['remove_membership_id']);

            Flash::success('Uživatel byl odebrán z týmu');
            Url::redirect('/{tenant}/teams/' . $id . '/edit/#changelist');
        }

        Url::redirect('/{tenant}/teams/' . $id . '/edit/#changelist');
    }

    /* ==========================================================
     * AKTIVACE / DEAKTIVACE
     * ========================================================== */

    /**
     * Přepne aktivní stav týmu (aktivace/deaktivace).
     * Deaktivace týmu by měla zachovat historická data.
     *
     * Vedlejší efekty:
     * - Mění stav `active` v tabulce týmů
     * - Nastavuje flash zprávu
     * - Může ovlivnit fungování závislých funkcí (např. přiřazování úkolů)
     *
     * TODO: [BUSINESS] Po zavedení workflow pravidel zkontrolovat, zda lze deaktivovat tým s otevřenými úkoly.
     *
     * @param int $id ID týmu k přepnutí stavu
     * @return void
     */
    public function toggle(int $id): void
    {
        $model = new TeamModel();
        $team  = $model->find($id);

        if ($team === null) {
            Flash::error('Tým nenalezen.');
            Url::redirect('/{tenant}/teams/#main');
        }

        $model->setActive($id, !(bool) $team['active']);

        Flash::success(
          $team['active'] === 1
                ? 'Tým byl deaktivován.'
                : 'Tým byl aktivován.'
        );

        Url::redirect('/{tenant}/teams/#main');
    }

    /* helper pro ošetření vstupu barvy */
	private function isValidHexColor(string $color): bool
	{
		// 'i' modifikátor = case-insensitive
		if (preg_match('/^#([0-9A-F]{3}|[0-9A-F]{6})$/i', $color))
		{
			return true;
		}
 		return false;
	}

    /**
     * @return string
     */
   public static function getDefaultColor()
   {
   	return self::DEFAULT_COLOR;
   }

    
}