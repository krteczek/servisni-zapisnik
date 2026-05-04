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
use App\Core\Transaction;

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

    public function createFormGet(int $orderId): string
    {   
        $order = []; 
        $tasks = []; 
        $teams = []; 
        
        $order = $this->getOrderOrRedirect($orderId);

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
        Flash::error('Zakázka neexistuje');
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
    $result = null;

    // 1️⃣ Guard na zakázku
    $order = $this->getOrderOrRedirect($workOrderId);

    // 2️⃣ Validace
    $post = $this->validateTask($data);
    $teamId = (int) $post['team_id'];

    $teamModel = new TeamModel();
    $teams     = $teamModel->byActive(true);
    $team      = $teamModel->find($teamId);

    if (!$team) {
        $this->addError('team_id', 'Vybraný tým neexistuje');
    }

    // data zpět do view
    $this->view->post  = $post;
    $this->view->teams = $teams;
    $this->view->order = $order;

    if ($this->hasErrors()) {
        return $this->render('tasks/create');
    }

    try {
        // 3️⃣ TRANSACTION
        $result = Transaction::run(function () use ($post, $teamId, $workOrderId) {

            $taskModel = new TaskModel();

            // 🧱 vytvoření tasku
            $taskId = $taskModel->create([
                'title'              => $post['title'],
                'description'        => $post['description'],
                'team_id'            => $teamId,
                'work_order_id'      => $workOrderId,
                'created_by_user_id' => Auth::id(),
                'is_recurring'       => $post['is_recurring'],
            ]);

            if (!$taskId) {
                throw new \RuntimeException('Task create failed');
            }

            // 🔁 recurring
            if ((int) $post['is_recurring'] === 1) {

                $recurringModel = new RecurringTaskModel();

                $recurringId = $recurringModel->create([
                    // 'company_id'          => Auth::companyId(),
                    'task_id'             => $taskId,
                    'team_id'             => $teamId,
                    'frequency_type'      => Config::get('recurring.default.frequency_type'),
                    'frequency_value'     => Config::get('recurring.default.frequency_value'),
                    'next_due_date'       => Config::get('recurring.default.next_due_date'),
                    'warning_days_before' => Config::get('recurring.default.warning_days_before'),
                    'active'              => 1,
                ]);

                if (!$recurringId) {
                    throw new \RuntimeException('Recurring create failed');
                }

                $updated = $taskModel->update($taskId, [
                    'recurring_task_id' => $recurringId
                ]);

                if (!$updated) {
                    throw new \RuntimeException('Task update with recurring failed');
                }

                return [
                    'redirect' => '/{tenant}/tasks/' . $taskId . '/recurring',
                ];
            }

            // default redirect
            return [
                'redirect' => '/{tenant}/work-orders/' . $workOrderId . '/detail/#taskId_' . $taskId,
            ];
        });

    } catch (Throwable $e) {

        // 🔥 TECHNICKÝ LOG
        LoggerHolder::get()->error('TaskController.saveTask FAILED', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
            'input'   => serialize($data),
        ]);

        // 👤 USER MESSAGE
        $this->addError('global', 'Nepodařilo se uložit úkol, omlouváme se. Zkuste to prosím znovu.');

        return $this->render('tasks/create');
    }

    // 4️⃣ REDIRECT mimo transaction (SPRÁVNĚ)
    if (!$result || !isset($result['redirect'])) {
        return $this->render('tasks/create');
    }

    Url::redirect($result['redirect']);
}

 

    public function createFormPost(int $orderId): string
    {
    	 return $this->saveTask($_POST, $orderId);

    }
    
	// ověří existenci tasku, pokud existuje, vrátí jeho hodnoty, jinak redirect
	private function getTaskOrRedirect(int $taskId): array
	{
		$task = (new TaskModel())->find((int) $taskId);
		if(!$task) {
            Flash::error('Úkol neexistuje');
            Url::redirect('/{tenant}/tasks');			
		}
		return $task;	
	}

    private function ensureTaskEditable(array $task): void
    {
        if ($task['status'] === 'done') {
            Flash::info('Tento úkol nelze upravovat, protože je dokončený.');
            Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail');
        }

        if ($task['status'] === 'cancelled') {
            Flash::error('Tento úkol nelze upravovat, protože je zrušený.');
            Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail');
        }

        if (!empty($task['recurring_task_id']) && $task['is_recurring'] != 1) {
            Flash::info('Automaticky vygenerovaný úkol nelze upravovat.');
            Url::redirect('/{tenant}/work-orders');
        }
    }

	public function editTaskGet(int $taskId): string 
	{
		//ověříme že úkol existuje
		$task = $this->getTaskOrRedirect($taskId);
	    $this->ensureTaskEditable($task);

		//potřebujeme vytáhnout zakázku (podle work_order_id)
		$order = (new WorkOrderModel())->find($task['work_order_id']);
		//print_r($task);
        if (!$order) {
            Flash::error('Zakázka neexistuje');
            Url::redirect('/{tenant}/work-orders');
        }
		

		//zjistíme jméno a barvu týmu
		$team = (new TeamModel())->find($task['team_id']);
        if (!$team) {
            Flash::error('Tým neexistuje');
            Url::redirect('/{tenant}/tasks');
        }

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

	    $this->ensureTaskEditable($task);		

		//potřebujeme vytáhnout zakázku (podle work_order_id)
		$order = (new WorkOrderModel())->find($task['work_order_id']);
		
		//zjistíme jméno a barvu týmu
		$team = (new TeamModel())->find($task['team_id']);
		$post = $_POST;
        //nelze změnit tým, takže pro validaci 
        //musíme nastavit původní team_id
        $post['team_id'] = (int)$task['team_id'];
        
		$post = $this->validateTask($post);

        $title 			= $post['title'];
        $description 	= $post['description'];
        $team_id 		= $post['team_id'];

		$this->checkCsrf();

	  if ($this->hasErrors())
	  {
		$this->view->team = $team;
		$this->view->order = $order;
		$this->view->task = $task;
		$this->view->post = $post;
	     return $this->render('tasks/edit');
	  }

       $row = (new TaskModel())->update($taskId, [
            'title'              => $title,
            'description'        => $description,
            'team_id'            => $team_id,  
        ]);
        
        if(!$row)
        {
            $this->view->team = $team;
            $this->view->order = $order;
            $this->view->task = $task;
            $this->view->post = $post;

            $this->addError('global', 'Litujeme, úkol se nepodařilo vytvořit, zkuste to prosím později znovu.');
            return $this->render('tasks/edit');
        }

	    Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#taskId_' . $taskId);
  }

    public function cloneTaskGet(int $taskId): string
    {
        $task = $this->getTaskOrRedirect($taskId);
        // $this->ensureTaskEditable($task);

        $order = (new WorkOrderModel())->find($task['work_order_id']);

        if (!in_array($order['status'], ['new', 'in_progress'], true)) {
            Flash::error('Zakázka je uzavřená, nelze klonovat.');
            Url::redirect('/{tenant}/work-orders/' . $order['id'] . '/detail');
        }
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
        // $this->ensureTaskEditable($task);
        
        $order = (new WorkOrderModel())->find($task['work_order_id']);

        if (!in_array($order['status'], ['new', 'in_progress'], true)) {
            Flash::error('Zakázka je uzavřená, nelze klonovat.');
            Url::redirect('/{tenant}/work-orders/' . $order['id'] . '/detail');
        }
    
        return $this->saveTask($_POST, (int) $task['work_order_id']);
    }


    private function validateTask(array $data): array
    {
        $title           = trim($data['title'] ?? '');
        $description     = trim($data['description'] ?? '');
        $is_recurring    = (int) ($data['is_recurring'] ?? 0);
        $team_id         = (int) ($data['team_id'] ?? 0);  
        //print_r($data);
        if ($title === '') {
            $this->addError('title', 'Název úkolu je povinný');
        }

        if (mb_strlen($title) > 250) {
            $this->addError('title', 'Název úkolu je příliš dlouhý');
        }

        if (mb_strlen($description) > 10000) {
            $this->addError('description', 'Popis úkolu je příliš dlouhý');
        }

        if ($team_id === 0) {
            $this->addError('team_id', 'Tým je povinný');
        }


        return [
            'title' => $title,
            'description' => $description,
            'is_recurring' => $is_recurring,
            'team_id' => $team_id,
        ];
    }


    public function done(int $taskId): void
    {
        $task = $this->getTaskOrRedirect($taskId);
        $this->ensureTaskEditable($task);

        $ok = (new TaskModel())->closeTask($taskId, 'done');

        if (!$ok) {
            Flash::error('Nepodařilo se uzavřít úkol.');
            Url::back();
        }

        Flash::success('Úkol byl uzavřen.');
        Url::back();
    }

    public function cancel(int $taskId): void
    {
        $task = $this->getTaskOrRedirect($taskId);
        $this->ensureTaskEditable($task);

        $ok = (new TaskModel())->closeTask($taskId, 'cancelled');
        if (!$ok) {
            Flash::error('Nepodařilo se zrušit úkol.');
            Url::back();
        }

        Flash::success('Úkol byl zrušen.');
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
        if ($task['status'] !== 'open' && $task['status'] !== 'in_progress') {
            Flash::error('K tomuto úkolu již nelze přidat report.');
            Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail');
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

        $hours   = trim($participantData['hours'] ?? '');
        $minutes = trim($participantData['minutes'] ?? '');

        if ($hours === '' && $minutes === '') {
            $this->addError("participants.$userId", 'Vyplň čas nebo odškrtni pracovníka');
            continue;
        }

        if ($hours !== '' && (!is_numeric($hours) || $hours < -24 || $hours > 24)) {
            $this->addError("participants.$userId", 'Hodiny musí být v rozmezí -24 až 24');
        }

        if ($minutes !== '' && (!is_numeric($minutes) || $minutes < -59 || $minutes > 59)) {
            $this->addError("participants.$userId", 'Minuty musí být v rozmezí -59 až 59');
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
            'frequency_type' =>
            array_key_exists($data['frequency_type'] ?? '', $defaults['frequencies']) 
            //array_key_exists($data['frequency_type'], $defaults['frequencies'])
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
