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

class TaskController extends Controller
{
public function index(): string
{
    $tasks = (new TaskModel())->forIndex();

    $this->view->tasks = $tasks;
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
private function getOrderOrRedirect(int $orderId): array
{
    if ($orderId <= 0) {
        Url::redirect('/{tenant}/work-orders');
    }
		// máme tu čistý find (na basemodel), musíme ověřit, jestli je zakázka editovatelná.
    $order = (new workOrderModel())->find($orderId);

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



	private function setViewForDetail(int $orderId): void
	{
	    $order = $this->getOrderOrRedirect($orderId);
	
	    $taskModel = new TaskModel();
	    $tasks = $taskModel->forWorkOrderWithStats($orderId);
	
	    $teamModel = new TeamModel();
	    $teams = $teamModel->byActive(true);
	
	    $this->view->order = $order;
	    //$this->view->tasks = $tasks;
	    $this->view->teams = $teams;
	    
		
	    // data formuláře (pro sticky input / chyby)
	    $this->view->taskFormData   = $this->view->taskFormData   ?? [];
	    $this->view->taskFormErrors = $this->view->taskFormErrors ?? [];
	
	}

    public function createFormPost(?int $orderId): string
    {/*
$order = $workOrderModel->find($orderId);

if (!$workOrderModel->canAddTask($order)) {
    $this->flashError('K uzavřené zakázce nelze přidat nový úkol.');
    return $this->redirect("/work-orders/{$orderId}#main");
}*/
        $this->checkCsrf();
			
			$data = $_POST;
			
        $title 			= trim($data['title'] ?? '');
        $description 	= trim($data['description'] ?? '');
        $team_id 			= (int) $data['team_id'];
        $work_order_id	= (int) $data['work_order_id'];
        
        
        
        
        if ($title === '') {
            $this->addError('title', 'Název úkolu je povinný');
        }
			if(mb_strlen($title) > 250) {
            $this->addError('title', 'Název úkolu je příliš dlouhý');
        }
        
        
			if(mb_strlen($description) > 10000)
			{
				$this->addError('description', 'Popis úkolu je příliš dlouhý');
			}
			
			$team = (new TeamModel())->find($team_id);
			if(!$team) {
				$this->addError('team_id', 'Vybraný tým neexistuje');
			}

			$team = (new WorkOrderModel())->find($work_order_id);
			if(!$team) {
				flash::error('Vybraná zakázka neexistuje');
				URL::redirect("/{tenant}/work-orders/#main");
			}
			
        if ($this->hasErrors()) {
            return $this->createForm();
        }
			
        (new TaskModel())->create([
            'title'              => $title,
            'description'        => $description,
            'team_id'            => (int) $team_id,
            'work_order_id'      => (int) $work_order_id,
            'created_by_user_id' => $this->view->user['id'],
        ]);

        Url::redirect('/dashboard');
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

	public function editGet(int $taskId): string 
	{
		$task = $this->getTaskOrRedirect($taskId);
		
		//potřebujeme vytáhnoutzakázku (podle work_order_id)
		$order = (new WorkOrderModel())->find($task['work_order_id']);
		//print_r($order);
		
		//zjistíme jméno
		$team = (new TeamModel())->find($task['team_id']);


		$this->view->team = $team;
		$this->view->order = $order;
		$this->view->tasks = $task;
		return $this->render('tasks/edit');
	}
	
	public function editPost(int $taskId): string 
	{
		$task = $this->getTaskOrRedirect($taskId);
		
		$this->view->tasks = $tasks;
		return $this->render('tasks/edit');
	}



    public function done(int $taskId): void
    {
        (new TaskModel())->markDone($taskId);
        Url::back();
    }

    public function cancel(int $taskId): void
    {
        (new TaskModel())->cancel($taskId);
        Url::back();
    }
 public function addTaskReportGet(int $taskId): string
{
    
    //prostě zavoláme pomocnou metodu
    return $this->addTaskReport($taskId);
    //return $this->render('tasks/report');
}   

/**
 * Uložení nového reportu k úkolu
 */
public function addTaskReportPost(int $taskId): string
{
    $this->checkCsrf();
    $data = $_POST;
    // 1. Načti úkol
    $taskModel = new TaskModel();
    $task = $taskModel->find($taskId);
    
    if (!$task) {
        Flash::error('Úkol neexistuje');
        Url::redirect('/{tenant}/tasks');
    }
    
    // 2. Validace reportu (povinné)
		$report 				= trim($data['report'] ?? '');
		$kilometers 		= (int) $data['kilometers'] ?? 0;
		$participants		= $data['participants'] ?? [];
		$hours				= 0;
		$minutes				= 0;

    if (empty($report)) {
        $this->addError('report', 'Text reportu je povinný');
    }
    
    // 3. Validace kilometrů (nepovinné, ale pokud jsou, musí být číslo)
	if ((int) $kilometers < 0 || $kilometers > 9999) {
		$this->addError('kilometers', 'Kilometry musí být v rozmezí 0-9999');
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
                if ($hours !== '' && (!is_numeric($hours) || $hours < 0 || $hours > 24)) {
                    $this->addError("participants[$userId][hours]", 'Hodiny musí být 0-24');
                }
                // Validace minut
                if ($minutes !== '' && (!is_numeric($minutes) || $minutes < 0 || $minutes > 59)) {
                    $this->addError("participants[$userId][minutes]", 'Minuty musí být 0-59');
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

// Připravíme data pro účastníky
/*
$participantsData = [];
foreach ($_POST['participants'] as $userId => $participantData) {
    if (!empty($participantData['selected'])) {
        $participantsData[$userId] = [
            'selected' => true,
            'hours' => $participantData['hours'] ?? 0,
            'minutes' => $participantData['minutes'] ?? 0
        ];
    }
}

$data = [
    'report' => $report,
    'kilometers' => $kilometers,
    'participants' => $participantsData
];
*/
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
}
