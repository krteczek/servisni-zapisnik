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
        Url::redirect('/{tenant}/work-orders/#main');
    }
		// máme tu čistý find (na basemodel), musíme ověřit, jestli je zakázka editovatelná.
    $order = (new WorkOrderModel())->find($orderId);

    if (!$order) {
        Flash::error('Zakázka neexistuje');
        Url::redirect('/{tenant}/work-orders/#main');
    }
	
	if($order['status'] === 'new' || $order['status'] === 'in_progress') 
	{
		return $order;
	}
	Flash::error('Tato zakázka již byla ukončena a proto k ní nelze přidat nový úkol.');
	Url::redirect('/{tenant}/work-orders/#main');

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
                'due_date'           => $post['due_date'],
            ]);

            if (!$taskId) {
                throw new \RuntimeException('Task create failed');
            }

            // aktualizace stavu zakázky na "in_progress", pokud ještě není
            $WO = new WorkOrderModel();
            $WO->update($workOrderId, [
                'status' => 'in_progress',
            ]);

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
   
    
    public function createTaskFromOrderGet(int $orderId):string
    {
        $order = $this->getOrderOrRedirect($orderId);
        if ($order['status'] !== 'new') {
            Flash::error('Jen u nové zakázky lze vytvořit úkol ze zakázky.');
            Url::to('/{tenant}/work-orders/' . $orderId . '/detail/#main');
        }
        $post['title'] = $order['title'];
        $post['description'] = $order['description'];
        $post['due_date'] = $order['wo_due_date'];
        $this->view->post = $post;
        $this->view->order = $order;
        return $this->render('tasks/create');


    }
    public function createTaskFromOrderPost(int $orderId):string
    {
        $order = $this->getOrderOrRedirect($orderId);
        if ($order['status'] !== 'new') {
            Flash::error('Jen u nové zakázky lze vytvořit úkol ze zakázky.');
            Url::to('/{tenant}/work-orders/' . $orderId . '/detail/#main');
        }

        return $this->saveTask($_POST, $orderId);

    }
	// ověří existenci tasku, pokud existuje, vrátí jeho hodnoty, jinak redirect
	private function getTaskOrRedirect(int $taskId): array
	{
		$task = (new TaskModel())->find((int) $taskId);
		if(!$task) {
            Flash::error('Úkol neexistuje');
            Url::redirect('/{tenant}/tasks/#main');			
		}
		return $task;	
	}

    private function ensureTaskEditable(array $task): void
    {
        if ($task['status'] === 'done') {
            Flash::info('Tento úkol nelze upravovat, protože je dokončený.');
            Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main');
        }

        if ($task['status'] === 'cancelled') {
            Flash::error('Tento úkol nelze upravovat, protože je zrušený.');
            Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main');
        }

        if (!empty($task['recurring_task_id']) && $task['is_recurring'] != 1) {
            Flash::info('Automaticky vygenerovaný úkol nelze upravovat.');
            Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main');
        }
    }
    private function ensureTaskClosable(array $task, string $action = 'done'):void
    {
        $label = $action === 'done' ? 'uzavřít' : 'zrušit';

//print_r($task);exit;

        if ($task['status'] === 'done') {
            Flash::info("Tento úkol nelze $label, protože je již dokončený.");
            Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main');
        }

        if ($task['status'] === 'cancelled') {
            Flash::error("Tento úkol nelze $label, protože je již zrušený.");
            Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main');
        }
        
        

        if (!empty($task['is_recurring']) && $task['is_recurring'] == 1) {

            Flash::info(
                'Opakující se master úkol nelze tímto způsobem uzavřít ani zrušit. '
                . 'Pro ukončení opakování deaktivujte opakování v nastavení opakování úkolu.'
            );

            Url::redirect( 
                '/{tenant}/tasks/' . $task['id'] . '/recurring/#main'
            );
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
            Url::redirect('/{tenant}/work-orders/#main');
        }
		

		//zjistíme jméno a barvu týmu
		$team = (new TeamModel())->find($task['team_id']);
        if (!$team) {
            Flash::error('Tým neexistuje');
            Url::redirect('/{tenant}/tasks/#main');
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
            'title'              => $post['title'],
            'description'        => $post['description'],
            'team_id'            => $post['team_id'],
            'due_date'           => $post['due_date'], 
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
        if (!$order) {
            Flash::error('Zakázka neexistuje.');
            Url::redirect('/{tenant}/work-orders/#main');
        }

        if (!in_array($order['status'], ['new', 'in_progress'], true)) {
            Flash::error('Zakázka je uzavřená, nelze klonovat.');
            Url::redirect('/{tenant}/work-orders/' . $order['id'] . '/detail/#main');
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
        if (!$order) {
            Flash::error('Zakázka neexistuje.');
            Url::redirect('/{tenant}/work-orders/#main');
        }

        if (!in_array($order['status'], ['new', 'in_progress'], true)) {
            Flash::error('Zakázka je uzavřená, nelze klonovat.');
            Url::redirect('/{tenant}/work-orders/' . $order['id'] . '/detail/#main');
        }
    
        return $this->saveTask($_POST, (int) $task['work_order_id']);
    }


    private function validateTask(array $data): array
    {
        $title           = trim($data['title'] ?? '');
        $description     = trim($data['description'] ?? '');
        $is_recurring    = (int) ($data['is_recurring'] ?? 0);
        $team_id         = (int) ($data['team_id'] ?? 0);
        $dueDate         = $this->validateDate($data['due_date'] ?? null);
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
            'due_date' => $dueDate,
        ];
    }

private function validateDate(?string $date): ?string
{   
    //print_r($date);
    $date = trim($date ?? '');

    if ($date === '') {
        
        return null;
    }

    $dt = \DateTime::createFromFormat('Y-m-d', $date);

    $isValid =
        $dt !== false
        && $dt->format('Y-m-d') === $date;
    if (!$isValid) {
        return null;
    }

    return $date;
}


    public function done(int $taskId): void
    {
        $task = $this->getTaskOrRedirect($taskId);
        $this->ensureTaskClosable($task, 'done');

        try {

            $ok = (new TaskModel())->closeTask($taskId, 'done');

            Flash::success('Úkol byl uzavřen.');
            Url::back();
        } catch (Throwable $e) {

                // 🔥 TECHNICKÝ LOG
                LoggerHolder::get()->error('TaskController.done FAILED', [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                    'trace'   => $e->getTraceAsString(),
                    'input'   => serialize($task),
                ]);

                Flash::error('Nepodařilo se uzavřít úkol.');
                Url::back();


        }
    }

    public function cancel(int $taskId): void
    {
        $task = $this->getTaskOrRedirect($taskId);
        $this->ensureTaskClosable($task, 'cancel');

        try {
            $ok = (new TaskModel())->closeTask($taskId, 'cancelled');
 
            Flash::success('Úkol byl zrušen.');
            Url::back();
        } catch (Throwable $e) {

                // 🔥 TECHNICKÝ LOG
                LoggerHolder::get()->error('TaskController.cancel FAILED', [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                    'trace'   => $e->getTraceAsString(),
                    'input'   => serialize($task),
                ]);

                Flash::error('Nepodařilo se zrušit úkol.');
                Url::back();
        }
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
            Url::redirect('/{tenant}/tasks/#main');
        }
        if ($task['status'] !== 'open' && $task['status'] !== 'in_progress') {
            Flash::error('K tomuto úkolu již nelze přidat report.');
            Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main');
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
            Url::redirect('/{tenant}/tasks/#main');
        }

        if ($task['status'] !== 'open' && $task['status'] !== 'in_progress') {
            Flash::error('K tomuto úkolu již nelze přidat report.');
            Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main');
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