<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Team;
use App\Models\TeamMembership;
use App\Models\UserModel;
use App\Core\Url;
use App\Core\Config;
class TeamController extends Controller
{

public function index(): string
{
    $teamModel = new Team();
    $membershipModel = new TeamMembership();

    $teams = $teamModel->all();

    foreach ($teams as &$team) {
        $team['members'] = $membershipModel->currentMembers((int)$team['id']);
        $team['members_count'] = count($team['members']);
    }

    $this->view->teams = $teams;

    return $this->render('teams/index');
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
            $this->view->error = 'Název týmu je povinný';
            return $this->render('teams/create');
        }
		$team = new Team();
        $team->create($name, $color);
			Url::redirect('/teams');
    }

public function edit(int $id): string
{
    $teamModel = new Team();
    $membershipModel = new TeamMembership();
    $userModel = new UserModel();

    // aktuální tým
    $this->view->team = $teamModel->find($id);

    // členové tohoto týmu
    $this->view->members = $membershipModel->currentMembers($id);

    // role v týmu
    $this->view->rolesInTeam = Config::get('roles_in_team')['roles'];

    // uživatelé, které lze přidat
    $this->view->availableUsers = $userModel->availableForTeam($id);

    // 🔥 NOVÉ: aktivní týmy všech uživatelů (pro barevné tečky)
    $this->view->userTeams = $membershipModel->activeTeamsByUsers();

    return $this->render('teams/edit');
}

public function update(int $id): string
{
    $membershipModel = new TeamMembership();
    $teamModel = new Team();

    $action = $_POST['_action'] ?? null;

    switch ($action) {

        case 'update_team':
            $teamModel->updateTeam($id, [
                'name'  => trim($_POST['name']),
                'color' => trim($_POST['color']),
            ]);
            break;

        case 'add_member':
            $membershipModel->add(
                (int)$_POST['add_user_id'],
                $id,
                $_POST['role_in_team'] ?? 'member'
            );
            break;

        case 'change_role':
            $membershipModel->change_user_role(
                (int)$_POST['change_user_role'],
                ['role_in_team' => $_POST['role_in_team']]
            );
            break;

        case 'remove_member':
            $membershipModel->end(
                (int)$_POST['remove_membership_id']
            );
            break;
    }

    return Url::redirect('/teams/' . $id . '/edit');
}

}
