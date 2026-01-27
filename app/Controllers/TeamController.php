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

final class TeamController extends Controller
{
    /* ==========================================================
     * VÝPIS AKTIVNÍ / NEAKTIVNÍ
     * ========================================================== */

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

    /* ==========================================================
     * VYTVOŘENÍ TÝMU
     * ========================================================== */

    public function create(): string
    {
        return $this->render('teams/create');
    }

    public function store(): void
    {
        $this->checkCsrf();

        $name  = trim($_POST['name'] ?? '');
        $color = trim($_POST['color'] ?? '#2196F3');

        if ($name === '') {
            Flash::add('error', 'Název týmu je povinný.');
            Url::redirect('/teams/create');
        }

        (new Team())->create($name, $color);

        Flash::add('success', 'Tým byl vytvořen.');
        Url::redirect('/teams');
    }

    /* ==========================================================
     * EDITACE TÝMU
     * ========================================================== */

    public function edit(int $id): string
    {
        $teamModel       = new Team();
        $membershipModel = new TeamMembership();
        $userModel       = new UserModel();

        $team = $teamModel->find($id);
        if (!$team) {
            Flash::add('error', 'Tým neexistuje.');
            Url::redirect('/teams');
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

    public function update(int $id): void
    {
        $this->checkCsrf();

        $teamModel       = new Team();
        $membershipModel = new TeamMembership();
        $userModel       = new UserModel();

        if (!$teamModel->find($id)) {
            Flash::add('error', 'Tým neexistuje.');
            Url::redirect('/teams');
        }

        $roles     = Config::get('roles_in_team')['roles'];
        $default   = Config::get('roles_in_team')['default'];

        /* ===== ÚPRAVA NÁZVU / BARVY ===== */
        if (isset($_POST['name'], $_POST['color'])) {
            $name  = trim($_POST['name']);
            $color = trim($_POST['color']);

            if ($name !== '') {
                $teamModel->updateTeam($id, [
                    'name'  => $name,
                    'color' => $color,
                ]);
            }

            Url::redirect('/teams/' . $id . '/edit');
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
            if (!$user || $user['global_role'] === 'root') {
                Url::redirect('/teams/' . $id . '/edit');
            }

            $membershipModel->add($userId, $id, $role);
            Url::redirect('/teams/' . $id . '/edit');
        }

        /* ===== ZMĚNA ROLE ===== */
        if (isset($_POST['change_user_role'], $_POST['role_in_team'])) {
            $membershipId = (int) $_POST['change_user_role'];
            $role         = $_POST['role_in_team'];

            if (isset($roles[$role])) {
                $membershipModel->changeRole($membershipId, $role);
            }

            Url::redirect('/teams/' . $id . '/edit');
        }

        /* ===== ODEBRÁNÍ ČLENA ===== */
        if (isset($_POST['remove_membership_id'])) {
            $membershipModel->end((int) $_POST['remove_membership_id']);
            Url::redirect('/teams/' . $id . '/edit');
        }

        Url::redirect('/teams/' . $id . '/edit');
    }

    /* ==========================================================
     * AKTIVACE / DEAKTIVACE
     * ========================================================== */

    public function toggle(int $id): void
    {
        $model = new Team();
        $team  = $model->find($id);

        if (!$team) {
            Flash::add('error', 'Tým nenalezen.');
            Url::redirect('/teams');
        }

        $model->setActive($id, !(bool) $team['active']);

        Flash::add(
            'success',
            $team['active']
                ? 'Tým byl deaktivován.'
                : 'Tým byl aktivován.'
        );

        Url::redirect('/teams');
    }
}
