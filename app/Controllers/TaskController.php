<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

use App\Models\TaskModel;
use App\Models\TeamModel;
use App\Models\WorkOrderModel;
use App\Models\AssignmentModel;
use App\Core\Url;
use App\Core\Flash;
use App\Core\Auth;
use App\Core\Roles;


class TaskController extends Controller
{
	public function index(): string
	{
	    $tasks = (new TaskModel())->forIndex();
	//var_dump($tasks);
	    $this->view->data = $tasks;
	    return $this->render('tasks/index');
	}

    public function createFormGet(?int $orderId = null): string
    {
    		$order = $this->getOrderOrRedirect($orderId);
    		
			$taskModel = new TaskModel();
			$tasks = $taskModel->forWorkOrderWithStats($orderId);
	
			$teamModel = new TeamModel();
			$teams = $teamModel->byActive(true);
	
			$this->view->order = $order;
			$this->view->tasks = $tasks;
			$this->view->teams = $teams;
	    	
			return $this->render('tasks/create');
    }
private function getOrderOrRedirect(?int $orderId): array
{
    if (!$orderId || $orderId <= 0) {
        Url::redirect('/{tenant}/work-orders');
    }
		// máme tu čistý find (na basemodel), musíme ověřit, jestli je zakázka editovatelná.
    $order = (new WorkOrderModel())->find($orderId);

    if (!$order) {
        Flash::error('Zakázka neexistuje');
        Url::redirect('/{tenant}/work-orders');
    }
	
	if($order['status'] === 'new' || $order['status'] === 'in_progress') 
	{
		return $order;
	}
	Flash::error('Tato zakázka již byla ukončena a proto k ní nelze přidat nový úkol. Pokud je to nutné, lze zakázku přepnout do stavu ');
	Url::redirect('/{tenant}/work-orders');

}

private function saveTask(array $data, int $workOrderId): string
{
	$this->checkCsrf();
    $order = $this->getOrderOrRedirect($workOrderId);

    $post = $this->validateTask($data);

    $team_id = (int) ($data['team_id'] ?? 0);

    //$teams = (new TeamModel())->find($team_id);
    $teams = (new TeamModel())->byActive(true);
	$team = (new TeamModel())->find($team_id);
	if (!$team) {
	    $this->addError('team_id', 'Vybraný tým neexistuje');
	}
  	$this->view->post = $data;
  	$this->view->teams = $teams;
  	$this->view->order = $order;


    if ($this->hasErrors()) {

       return $this->render('tasks/create');
    }

    $row = (new TaskModel())->create([
        'title'              => $post['title'],
        'description'        => $post['description'],
        'team_id'            => $team_id,
        'work_order_id'      => $workOrderId,
        'created_by_user_id' => Auth::id(),
    ]);

        if(!$row)
        {
           $this->addError('global', 'Litujeme, úkol se nepodařilo vytvořit, zkuste to prosím později znovu.');
           return $this->render('tasks/create');
        }
        Url::redirect('/{tenant}/work-orders/' . $workOrderId . '/detail/#taskId_' . $row);

}
    public function createFormPost(?int $orderId): string
    {
    	 return $this->saveTask($_POST, $orderId);

    }
    
	// ověří existenci tasku, pokud existuje, vrátí jeho hodnoty, jinak redirect
	private function getTaskOrRedirect(int $taskId): array
	{
		$task = (new TaskModel())->find((int) $taskId);
		//var_dump($task);
		//exit;
		if(!$task) {
        Flash::error('Úkol neexistuje');
        Url::redirect('/{tenant}/tasks');			
		}
		return $task;	
	}

	public function editTaskGet(int $taskId): string 
	{
		//ověříme že úkol existuje
		$task = $this->getTaskOrRedirect($taskId);
		
		//potřebujeme vytáhnoutzakázku (podle work_order_id)
		$order = (new WorkOrderModel())->find($task['work_order_id']);
		//print_r($task);
		
		//zjistíme jméno a barvu týmu
		$team = (new TeamModel())->find($task['team_id']);

		//print_r($team);
		$this->view->team = $team;
		$this->view->order = $order;
		$this->view->task = $task;
		$this->view->post = $task;
		return $this->render('tasks/edit');
	}
	
	public function editTaskPost(int $taskId): string 
	{
		$task = $this->getTaskOrRedirect($taskId);
		//$row = $this->saveTask($_POST, $task['work_order_id']);
		$post = $this->validateTask($_POST);
      $title 			= trim($post['title'] ?? '');
      $description 	= trim($post['description'] ?? '');

		$this->checkCsrf();

	  if ($this->hasErrors())
	  {
	     return $this->render('tasks/edit');
	  }

       $row = (new TaskModel())->update($taskId, [
            'title'              => $title,
            'description'        => $description,
        ]);
        
        if(!$row)
        {
           $this->addError('global', 'Litujeme, úkol se nepodařilo vytvořit, zkuste to prosím později znovu.');
           return $this->render('tasks/edit');
        }

	    Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#taskId_' . $taskId);
  }

public function cloneTaskGet(int $taskId): string
{
    $task = $this->getTaskOrRedirect($taskId);

    $order = (new WorkOrderModel())->find($task['work_order_id']);
    $teams = (new TeamModel())->byActive(true);

    // předvyplníme formulář
    $this->view->order = $order;
    $this->view->teams = $teams;
    $this->view->post = [
        'title' => $task['title'] . ' (kopie)',
        'description' => $task['description'],
        'team_id' => $task['team_id'],
    ];

    return $this->render('tasks/create');
}

public function cloneTaskPost(int $taskId): string
{
	$task = $this->getTaskOrRedirect($taskId);
	return $this->saveTask($_POST, (int) $task['work_order_id']);
 }


private function validateTask(array $data): array
{
    $title = trim($data['title'] ?? '');
    $description = trim($data['description'] ?? '');

    if ($title === '') {
        $this->addError('title', 'Název úkolu je povinný');
    }

    if (mb_strlen($title) > 250) {
        $this->addError('title', 'Název úkolu je příliš dlouhý');
    }

    if (mb_strlen($description) > 10000) {
        $this->addError('description', 'Popis úkolu je příliš dlouhý');
    }

    return [
        'title' => $title,
        'description' => $description,
    ];
}



    public function done(int $taskId): void
    {
        (new TaskModel())->closeTask($taskId, 'done');
        Url::back();
    }

    public function cancel(int $taskId): void
    {
        (new TaskModel())->closeTask($taskId, 'cancelled');
        Url::back();
    }
 public function addTaskReportGet(int $taskId): string
{
	//ověříme právo na přidání Reportu
	return $this->addTaskReport($taskId);

}   

/**
 * Uložení nového reportu k úkolu
 */
public function addTaskReportPost(int $taskId): string
{
	//ověříme právo na přidání Reportu

	
	//má právo, může dát Report
    $this->checkCsrf();
    $data = $_POST;
    
	$canUserAddReport = self::canUserAddReport($taskId);
	if(
	    !$canUserAddReport
    && !Roles::isManagement(Auth::role())
    ){
		//není manager, nemá ani vidět:
    	Flash::error('Nemáte oprávnění. Nejste členem týmu, který má úkol plnit...');
		Url::redirect('/{tenant}/tasks/#main');
	}
    $data['canUserAddReport'] = $canUserAddReport;
    // 1. Načti úkol
    $taskModel = new TaskModel();
    $task = $taskModel->find($taskId);
    if (!$task) {
        Flash::error('Úkol neexistuje');
        Url::redirect('/{tenant}/tasks');
    }
    
    // 2. Validace reportu (povinné)
		$report 				= trim($data['report'] ?? '');

		$kilometers 		= (int) ($data['kilometers'] ?? 0);
		$participants		= $data['participants'] ?? [];
		$hours				= 0;
		$minutes				= 0;

    if (empty($report)) {
        $this->addError('report', 'Text reportu je povinný');
    }
    
    // 3. Validace kilometrů (nepovinné, ale pokud jsou, musí být číslo)
	if ((int) $kilometers < -9999 || $kilometers > 9999) {
		$this->addError('kilometers', 'Kilometry musí být v rozmezí -9999 až 9999');
	}
    
    // 4. Validace účastníků (nepovinné, ale pokud jsou zaškrtnutí, musí mít čas)
    
    foreach ($participants as $userId => $participantData) {
        if (!empty($participantData['selected'])) {
            // Pokud je zaškrtnutý, musí mít čas
            $hours = isset($participantData['hours']) ? trim($participantData['hours']) : '';
            $minutes = isset($participantData['minutes']) ? trim($participantData['minutes']) : '';
            
            if ($hours === '' && $minutes === '') {
                $this->addError("participants[$userId]", 'Vyplň čas nebo odškrtni pracovníka');
            } else {
                // Validace hodin
                if ($hours !== '' && (!is_numeric($hours) || $hours < -24 || $hours > 24)) {
                    $this->addError("participants[$userId][hours]", 'Hodiny musí být v rozmezí -24 až 24');
                }
                // Validace minut
                if ($minutes !== '' && (!is_numeric($minutes) || $minutes < -59 || $minutes > 59)) {
                    $this->addError("participants[$userId][minutes]", 'Minuty musí být v rozmezí -59 až 59');
                }
            }
        }
    }
    
    	// 5. Pokud jsou chyby, vrať se zpět (bez reloadu)
	    if ($this->hasErrors()) {
	    	$this->view->data = $data;
			return $this->addTaskReport($taskId);
	    }
    
    
    
    // 6. Uložení reportu
// 6. Uložení reportu i s účastníky
$assignmentModel = new AssignmentModel();

$assignmentId = $assignmentModel->createReport($taskId, $task['work_order_id'] ?? 0, $data);
//
    
    if ($assignmentId) {
        Flash::success('Report byl úspěšně uložen');
    } else {
			$this->addError('global', 'Nepodařilo se uložit report');
			$this->view->data = $data;

			//můžeme zavolat render pro reporty, doplnit zpátky do proměnných
			return $this->addTaskReport($taskId);
    }
    //podařilo se uložit, uděláme redirect pod formulář
    Url::redirect('/{tenant}/tasks/' . $taskId . '/report/#report_list');
}

public function addTaskReport(int $taskId): string
{
    // Získáme data z db
    $taskModel = new TaskModel();
    $task = $taskModel->find($taskId);
    
    if (!$task) {
        Flash::error('Úkol neexistuje');
        Url::redirect('/{tenant}/tasks');
    }
	$task['canUserAddReport'] = self::canUserAddReport($taskId);
	if(
	    !$task['canUserAddReport']
    && !Roles::isManagement(Auth::role())
    ){
		//není manager, nemá ani vidět:
    	Flash::error('Nemáte oprávnění. Nejste členem týmu, který má úkol plnit...');
		Url::redirect('/{tenant}/tasks/#main');
	}

    

    // Načti členy týmu
    $teamModel = new TeamModel();
    $teamMembers = $teamModel->getActiveMembers($task['team_id']);
    
    // Načti existující reporty
    $assignmentModel = new AssignmentModel();
    $reports = $assignmentModel->findByTask($taskId);
    
    $this->view->task = $task;
    $this->view->teamMembers = $teamMembers;
    $this->view->reports = $reports;
    
    // Pokud máme stará data z POST (při chybě), předáme je do view
    if (isset($this->view->data)) {
        $this->view->oldData = $this->view->data;
    }
    
    return $this->render('tasks/report');
}

    private function canUserAddReport(int $taskId)
    {
    	//zízkáme id aktuálního přihlášeného uživatele
    	$userId = Auth::id();

    	//zjistíme, jestli má právo přidat report k tomuto úkolu
    	if((new TaskModel())->canUserAddReport((int) $taskId, (int) $userId) === true)
    	{
    		return true;
    	}
       return false;
    }

}
