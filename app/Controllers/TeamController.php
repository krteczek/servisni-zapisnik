<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Url;
use App\Models\Team;
use App\Models\TeamMembership;
use App\Models\UserModel;

class TeamController extends Controller
{
    /**
     * Přehled týmů
     */
    public function index(): string
    {
        $teamModel = new Team();

        $this->view->teams = $teamModel->allWithMembersCount();

        return $this->render('teams/index');
    }

    /**
     * Formulář pro vytvoření týmu
     */
    public function create(): string
    {
        return $this->render('teams/create');
    }

    /**
     * Uložení nového týmu
     */
    public function store(): string
    {
        $name  = trim($_POST['name'] ?? '');
        $color = trim($_POST['color'] ?? '#2196F3');

        if ($name === '') {
            $this->view->error = 'Název týmu je povinný';
            return $this->render('teams/create');
        }

        $teamModel = new Team();
        $teamModel->create($name, $color);

        Url::redirect('/teams');
    }

    /**
     * Detail / editace týmu + členové
     */
    public function edit(int $id): string
    {
        //$id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            return $this->forbidden();
        }

        $teamModel       = new Team();
        $membershipModel = new TeamMembership();
        $userModel       = new UserModel();

        $team = $teamModel->find($id);
        if (!$team) {
            return $this->forbidden();
        }

        $this->view->team = $team;
        $this->view->members = $membershipModel->currentMembers($id);
        $this->view->availableUsers = $userModel->availableForTeam($id);

        return $this->render('teams/edit');
    }

    /**
     * Přidání / odebrání členů týmu
     */
    public function update(int $id): string
    {
        //$id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            return $this->forbidden();
        }

        $membershipModel = new TeamMembership();

        // přidání člena
        if (!empty($_POST['add_user_id'])) {
            $membershipModel->add(
                (int) $_POST['add_user_id'],
                $id,
                $_POST['role_in_team'] ?? 'monter'
            );
        }

        // odebrání člena (ukončení platnosti)
        if (!empty($_POST['remove_membership_id'])) {
            $membershipModel->end((int) $_POST['remove_membership_id']);
        }

        Url::redirect('/teams/' . $id . '/edit');
    }
}
