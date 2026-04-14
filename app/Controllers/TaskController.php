<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

use App\Models\TaskModel;
use App\Models\TeamModel;
use App\Models\WorkOrderModel;
use App\Models\AssignmentModel;
use App\Models\RecurringTaskModel;
use App\Core\Url;
use App\Core\Flash;
use App\Core\Auth;
use App\Core\Roles;
use App\Core\Config;
use App\Core\LoggerHolder;

use Throwable;

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
			$this->view->post = $tasks;
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
	Flash::error('Tato zakázka již byla ukončena a proto k ní nelze přidat nový úkol.');
	Url::redirect('/{tenant}/work-orders');

}

private function saveTask(array $data, int $workOrderId): string
{
	$this->checkCsrf();
   $order = $this->getOrderOrRedirect($workOrderId);
//print_r($data);
   $post = $this->validateTask($data);

   $team_id = (int) ($data['team_id'] ?? 0);

   // vyhledáme týmy
   $TM = new TeamModel();
   $teams = $TM->byActive(true);
	$team  = $TM->find($team_id);
	if (!$team) {
	  $this->addError('team_id', 'Vybraný tým neexistuje');
	}

  	$this->view->post  = $data;
  	$this->view->teams = $teams;
  	$this->view->order = $order;


   if ($this->hasErrors()) {

     return $this->render('tasks/create');
   }
//print_r($post);
    $row = null;
    $TM = new TaskModel();
    $toDb1 = [
        'title'              => $post['title'],
        'description'        => $post['description'],
        'team_id'            => $team_id,
        'work_order_id'      => $workOrderId,
        'created_by_user_id' => Auth::id(),
        'is_recurring'       => $post['is_recurring'],
    ];
    try
    {
    		$row = $TM->create($toDb1);
    }
    catch (Throwable $e)
	   {
	   	LoggerHolder::get()->error('UserActivation failed', [
				    'message'   => $e->getMessage(),
				    'file'      => $e->getFile(),
				    'line'      => $e->getLine(),
				    'trace'     => $e->getTraceAsString(),
				    'toDb1'      => serialize($toDb1),

          ]);
          $this->addError('global', 'Litujeme, úkol se nepodařilo vytvořit, zkuste to prosím později znovu.');
          return $this->render('tasks/create');
	   }
//print_r($row);//exit;
 		if(!$row)
 		{
   		$this->addError('global', 'Litujeme, úkol se nepodařilo vytvořit, zkuste to prosím později znovu.');
   		return $this->render('tasks/create');
 		}

    if($post['is_recurring'] === 1)
    {
	   $datadb2 = [
	            'company_id' => Auth::companyId(),
	            'task_id' => $row,
	            'team_id' => $team_id,
	            'frequency_type' => Config::get('recurring.default.frequency_type'),
	            'frequency_value' => Config::get('recurring.default.frequency_value'),
	            'next_due_date' => Config::get('recurring.default.next_due_date'),
	            'warning_days_before' => Config::get('recurring.default.warning_days_before'),
	            'active' => 1,
	        ];
    	try
    	{
	   	$RM = new RecurringTaskModel();
	      $recurringId = $RM->create($datadb2);
	      $TM->update($row, [
	            'recurring_task_id' => $recurringId
	      ]);

	      // Načíst nastavení pro recurring
	      Url::redirect('/{tenant}/tasks/' . $row . '/recurring');
	   }
	   catch (Throwable $e)
	   {
	   	    LoggerHolder::get()->error('UserActivation failed', [
				    'message'   => $e->getMessage(),
				    'file'      => $e->getFile(),
				    'line'      => $e->getLine(),
				    'trace'     => $e->getTraceAsString(),
				    'toDb2'      => serialize($datadb2),
            ]);
            $this->addError('global', 'Litujeme, úkol se nepodařilo vytvořit, zkuste to prosím později znovu.');
	        return $this->render('tasks/create');   
        }
    }
    else
    {
    	Url::redirect('/{tenant}/work-orders/' . $workOrderId . '/detail/#taskId_' . $row);
    }
    return $this->render('tasks/create');
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
		
		if (!empty($task['recurring_task_id']) && $task['is_recurring'] != 1) {
		    Flash::error('Tento úkol byl vygenerován automaticky a nelze jej upravovat.');
		    Url::redirect('/{tenant}/tasks');
		}		
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
    $is_recurring = (int) ($data['is_recurring'] ?? 0);
    if ($title === '') {
        $this->addError('title', 'Název úkolu je povinný');
    }

    if (mb_strlen($title) > 250) {
        $this->addError('title', 'Název úkolu je příliš dlouhý');
    }

    if (mb_strlen($description) > 10000) {
        $this->addError('description', 'Popis úkolu je příliš dlouhý');
    }

    if ($title === '') {
        $this->addError('title', 'Název úkolu je povinný');
    }

    return [
        'title' => $title,
        'description' => $description,
        'is_recurring' => $is_recurring,
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


public function recurringGet(int $taskId): string
{
    $task = $this->getTaskOrRedirect($taskId);
    //var_dump($task);
    if (!$task['is_recurring']) {
        Flash::error('Tento úkol není nastaven jako opakovaný.');
        Url::redirect('/{tenant}/tasks');
    }

    $RM = new RecurringTaskModel();
    $recurring = ($RM->find($task['recurring_task_id']) ?? []);
    //var_dump($recurring);

    $this->view->task = $task;
    $this->view->data = $recurring;

    return $this->render('tasks/recurring');
}

public function recurringPost(int $taskId): string
{
    $this->checkCsrf();

    $task = $this->getTaskOrRedirect($taskId);

    $RM = new RecurringTaskModel();
    $recurring = $RM->find($task['recurring_task_id']);

    if (!$recurring) {
        Flash::error('Recurring konfigurace neexistuje.');
        Url::redirect('/{tenant}/tasks');
    }

    //validace
    $data = $this->validateRecurring($_POST, Config::get('recurring'));
    //uložení do db
    $toDb = [
        'frequency_type'       => $data['frequency_type'],
        'frequency_value'      => (int)$data['frequency_value'],
        'next_due_date'        => $data['next_due_date'],
        'warning_days_before'  => (int)$data['warning_days_before'],
        'active'               => $data['active'],
    ];
    try
    {
       $row = $RM->update($task['recurring_task_id'],$toDb);
       Flash::success('Opakování bylo uloženo');

       Url::redirect('/{tenant}/tasks/' . $task['id'] . '/edit/#main');
    }
    catch (Throwable $e)
	 {
	   	LoggerHolder::get()->error('TaskController.recurringPost: FAILED', [
				    'message'   => $e->getMessage(),
				    'file'      => $e->getFile(),
				    'line'      => $e->getLine(),
				    'trace'     => $e->getTraceAsString(),
				    'toDb1'      => serialize($toDb),

          ]);
          $this->addError('global', 'Litujeme, úkol se nepodařilo vytvořit, zkuste to prosím později znovu.');

	 }
    $this->view->task = $task;
    $this->view->data = $data;

	 return $this->render('tasks/recurring');

}

private function validateRecurring(array $data, array $defaults): array
{
    return [
        'frequency_type' => array_key_exists($data['frequency_type'], $defaults['frequencies'])
            ? $data['frequency_type']
            : $defaults['default']['frequency_type'],

        'frequency_value' => self::isBetween(
            $defaults['limits']['min_frequency_value'],
            $defaults['limits']['max_frequency_value'],
            (int)$data['frequency_value']
        ) ? (int)$data['frequency_value'] : $defaults['default']['frequency_value'],

        'warning_days_before' => self::isBetween(
            $defaults['limits']['min_warning_days'],
            $defaults['limits']['max_warning_days'],
            (int)$data['warning_days_before']
        ) ? (int)$data['warning_days_before'] : $defaults['default']['warning_days_before'],

        'next_due_date' => !empty($data['next_due_date'])
            ? $data['next_due_date']
            : $defaults['default']['next_due_date'],

        'active' => isset($data['active']) ? 1 : 0,
    ];
}

private static function isBetween($num1, $num2, $num3): bool
{
        $numbers = [$num1, $num2];

        if ($num3 >= min($numbers) && $num3 <= max($numbers)) {
            return true;
        } else {
            return false;
        }
}
}
