<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Url;
use App\Core\Config;
use App\Core\Flash;
use App\Models\Team;
use App\Models\TeamMembership;
use App\Models\UserModel;

class TeamController extends Controller
{
private function listByActive(bool $active): string
{
    $teamModel       = new Team();
    $membershipModel = new TeamMembership();

    $teams = $teamModel->byActive($active);

    foreach ($teams as $i => $team) {
        $members = $membershipModel->currentMembers((int) $team['id']);
        $teams[$i]['members']       = $members;
        $teams[$i]['members_count'] = count($members);
    }

    $this->view->teams = $teams;

    return $this->render('teams/index');
}

public function index(): string
{
    return $this->listByActive(true);
}

public function inactive(): string
{
    return $this->listByActive(false);
}

    public function create(): string
    {
        return $this->render('teams/create');
    }

    public function store(): string
    {
        $name  = trim($_POST['name'] ?? '');
        $color = trim($_POST['color'] ?? '#2196F3');

        if ($name === '') {
            $this->addError('name', 'Název týmu je povinný');
        }

        $this->checkCsrf();

        if ($this->hasErrors()) {
            return $this->render('teams/create');
        }

        if((new Team())->create($name, $color)) {
        	    Flash::add('success', 'Tým ' . $name . ' byl úspěšně vytvořen.');
				Url::redirect('/teams');
        	} else {
        	    Flash::add('error', 'Tým ' . $name . ' se nepodařilo vytvořit.');
			}				
				Url::redirect('/teams');

        
    }

    public function edit(int $id): string
    {
        $teamModel       = new Team();
        $membershipModel = new TeamMembership();
        $userModel       = new UserModel();

        $team = $teamModel->find($id);
        if (!$team) {
         	Flash::add('error', 'Vámi požadovaný tým neexistuje.');
				Url::redirect('/teams');
       }

        $this->view->team           = $team;
        $this->view->members        = $membershipModel->currentMembers($id);
        $this->view->availableUsers = $userModel->availableForTeam($id);
        $this->view->rolesInTeam    = Config::get('roles_in_team')['roles'];
        $this->view->userTeams      = $membershipModel->activeTeamsByUsers();

        return $this->render('teams/edit');
    }

    public function update(int $id): string
    {		
        $teamModel       = new Team();
        $membershipModel = new TeamMembership();
    		$team = $teamModel->find($id);
    if (!$team) {
        Flash::add('error', 'Vámi požadovaný tám nebyl nalezen.');
        Url::redirect('/teams');
    }

			$this->checkCsrf();
			// jediná logovaná chyba, pokud není, návrat na formulář
			if($this->hasErrors()) {
        		Url::redirect('/teams/' . $id . '/edit');				
			}


        /* ===== ÚPRAVA TÝMU ===== */
        if (isset($_POST['name'], $_POST['color'])) {
            $teamModel->updateTeam($id, [
                'name'  => trim($_POST['name']),
                'color' => trim($_POST['color']),
            ]);
        }

        /* ===== PŘIDÁNÍ ČLENA ===== */
        elseif (isset($_POST['add_user_id'])) {
            $membershipModel->add(
                (int) $_POST['add_user_id'],
                $id,
                $_POST['role_in_team'] ?? 'member'
            );
        }

        /* ===== ZMĚNA ROLE ===== */
elseif (isset($_POST['change_user_role'])) {
    $membershipModel->changeRole(
        (int) $_POST['change_user_role'],
        (string) $_POST['role_in_team']
    );
}

        /* ===== ODEBRÁNÍ ČLENA ===== */
        elseif (isset($_POST['remove_membership_id'])) {
            $membershipModel->end(
                (int) $_POST['remove_membership_id']
            );
        }
        

        Url::redirect('/teams/' . $id . '/edit');
    }
    
    public function toggle(int $id): void
{
	$model = new Team();
    $team = $model->find($id);

    if (!$team) {
        Flash::add('error', 'Tým nenalezen');
        Url::redirect('/teams');
    }

    $model->setActive($id, !(bool) $team['active']);
    Flash::add('success',
        $team['active']
            ? 'Tým: ' . $team['name'] . ' byl deaktivován'
            : 'Tým: ' . $team['name'] . ' byl aktivován'
    );

    Url::redirect('/teams');
}
}