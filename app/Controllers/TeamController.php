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
			$team = new Team();
        	$this->view->team = $team->find($id);
        	
        	$membershipModel = new TeamMembership();
        	$this->view->members = $membershipModel->currentMembers($id);
        	
        	$this->view->rolesInTeam = Config::get('roles_in_team')['roles'];
        	
        	$model = new UserModel;
        	$this->view->availableUsers = $model->availableForTeam($id);

        return $this->render('teams/edit');
    }

public function update(int $id): string
{
    $membershipModel = new TeamMembership();
    $teamModel = new Team();

    /* === UPDATE TÝMU === */
    if (isset($_POST['name'], $_POST['color'])) {
        $teamModel->updateRow($id, [
            'name'  => trim($_POST['name']),
            'color' => trim($_POST['color']),
        ]);
    }

    /* === PŘIDÁNÍ ČLENA === */
    if (!empty($_POST['add_user_id'])) {
        $membershipModel->add(
            (int)$_POST['add_user_id'],
            $id,
            $_POST['role_in_team'] ?? 'member'
        );
    }

    /* === ODEBRÁNÍ ČLENA === */
    if (!empty($_POST['remove_membership_id'])) {
        $membershipModel->end((int)$_POST['remove_membership_id']);
    }

    // 🚨 NIC nenastavujeme do view
    return Url::redirect('/teams/' . $id . '/edit');
}
}
