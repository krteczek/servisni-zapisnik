<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

use App\Models\TaskModel;
use App\Models\TeamModel;
use App\Models\WorkOrderModel;
use App\Models\TaskAssignmentModel;
use App\Models\RecurringTaskModel;
use App\Models\TeamMembership;
use App\Core\Url;
use App\Core\Flash;
use App\Core\Auth;
use App\Core\Roles;
use App\Core\Config;
use App\Core\LoggerHolder;
use App\Core\Transaction;
use App\Helpers\DateHelper;
use App\Services\Tasks\TaskType;
use App\Services\Tasks\TaskStatus;
use App\Services\WorkOrders\WOStatus;
use Throwable;
use App\Core\Types;


/**
 * @phpstan-import-type WorkOrderBaseRow from Types
 * @phpstan-import-type TaskBaseRow from Types 
 */
class TaskController extends Controller
{

	public function index(): string
	{
	    $tasks = (new TaskModel())->forIndex();
	//var_dump($tasks);
	    $this->view->tasks = $tasks;
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

/** 
 * @return WorkOrderBaseRow 
 */
private function getOrderOrRedirect(int $orderId): array
{
    if ($orderId <= 0) {
        Flash::error('Zakázka neexistuje');
        Url::redirect('/{tenant}/work-orders/#main');
    }
		// máme tu čistý find (na basemodel), musíme ověřit, jestli je zakázka editovatelná.
    $order = (new WorkOrderModel())->find($orderId);

    if ($order === null) {
        Flash::error('Zakázka neexistuje');
        Url::redirect('/{tenant}/work-orders/#main');
    }
	
	//if($order['status'] === 'new' || $order['status'] === 'in_progress') 
    if(WOStatus::isOpen($order['status']))
	{
		return $order;
	}
	Flash::error('Tato zakázka již byla ukončena a proto k ní nelze přidat nový úkol.');
	Url::redirect('/{tenant}/work-orders/#main');

}

/** 
 * @param array<string, mixed> $data 
 * @param int $workOrderId
 * @return string
 */ 
private function saveTask(array $data, int $workOrderId): string
{
    $this->checkCsrf();
    $redirect  = '';

    // 1️⃣ Guard na zakázku
    $order = $this->getOrderOrRedirect($workOrderId);

    // 2️⃣ Validace
    $post = $this->validateTask($data);
    $teamId = $post['team_id'];

    $teamModel = new TeamModel();
    $teams     = $teamModel->byActive(true);
    $team      = $teamModel->find($teamId);

    if ($team === null) {
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
        $redirect = Transaction::run(function () use ($post, $teamId, $workOrderId) {

            $taskModel = new TaskModel();

            $taskType = TaskType::NORMAL;
            if ((int) ($post['is_recurring_master'] ?? 0) === 1) {
                $taskType = TaskType::RECURRING_MASTER;
            }

            // 🧱 vytvoření tasku
            $taskId = $taskModel->create([
                'title'               => $post['title'],
                'description'         => $post['description'],
                'team_id'             => $teamId,
                'work_order_id'       => $workOrderId,
                'created_by_user_id'  => Auth::id(),
                'due_date'            => $post['due_date'],
                'task_type'           => $taskType 
            ]);
            if ($taskId <= 0) {
                throw new \RuntimeException('Task create failed');
            }

            // aktualizace stavu zakázky na "in_progress", pokud ještě není
            $WO = new WorkOrderModel();
            $updated = $WO->update($workOrderId, [
                'status' => WOStatus::IN_PROGRESS,
            ]);
           if (!$updated) {
                throw new \RuntimeException('Work order status update failed');
            }

            // 🔁 recurring
            if (TaskType::isMaster($taskType)) {

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

                if ($recurringId <= 0) {
                    throw new \RuntimeException('Recurring create failed');
                }

                $updated = $taskModel->update($taskId, [
                    'recurring_task_id' => $recurringId,
                    'task_type'         => TaskType::RECURRING_MASTER,
                ]);

                if (!$updated) {
                    throw new \RuntimeException('Task update with recurring failed');
                }

                return '/{tenant}/tasks/' . $taskId . '/recurring';
            }

            // default redirect
            return '/{tenant}/work-orders/' . $workOrderId . '/detail/#taskId_' . $taskId;
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


    Url::redirect($redirect);
}

 

    public function createFormPost(int $orderId): string
    {
    	 return $this->saveTask($_POST, $orderId);

    }
   
    
    public function createTaskFromOrderGet(int $orderId):string
    {
        $order = $this->getOrderOrRedirect($orderId);
        if (!WOStatus::isNew($order['status'])) {
            Flash::error('Jen u nové zakázky lze vytvořit úkol ze zakázky.');
            Url::redirect('/{tenant}/work-orders/' . $orderId . '/detail/#main');
        }
        $post = [];
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
        if (!WOStatus::isNew($order['status'])) {
            Flash::error('Jen u nové zakázky lze vytvořit úkol ze zakázky.');
            Url::redirect('/{tenant}/work-orders/' . $orderId . '/detail/#main');
        }

        return $this->saveTask($_POST, $orderId);

    }
	// ověří existenci tasku, pokud existuje, vrátí jeho hodnoty, jinak redirect
    /** 
     * @return TaskBaseRow
     */ 
    private function getTaskOrRedirect(int $taskId): array
	{
		$task = (new TaskModel())->find($taskId);
		if($task === null) {
            Flash::error('Úkol neexistuje');
            Url::redirect('/{tenant}/tasks/#main');			
		}
		return $task;	
	}

    /** 
     * @param TaskBaseRow $task 
     */
    private function ensureTaskEditable(array $task): void
    {
        if (TaskStatus::isDone($task['status'])) {
            Flash::info('Tento úkol nelze upravovat, protože je dokončený.');
            Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main');
        }

        if (TaskStatus::isCancelled($task['status'])) {
            Flash::error('Tento úkol nelze upravovat, protože je zrušený.');
            Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main');
        }

        if (!TaskType::isEditable($task['task_type'])) {
            Flash::info('Automaticky vygenerovaný úkol nelze upravovat.');
            Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main');
        }
    }

    /** 
     * @param TaskBaseRow $task 
     * 
     */
    private function ensureTaskClosable(array $task, string $action = 'done'):void
    {
        $label = $action === 'done' ? 'uzavřít' : 'zrušit';

//print_r($task);exit;

        if (TaskStatus::isDone($task['status'])) {
            Flash::info("Tento úkol nelze $label, protože je již dokončený.");
            Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main');
        }

        if (TaskStatus::isCancelled($task['status'])) {
            Flash::error("Tento úkol nelze $label, protože je již zrušený.");
            Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main');
        }
        
        

        if (TaskType::isMaster($task['task_type'])) {

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
        if ($order === null) {
            Flash::error('Zakázka neexistuje');
            Url::redirect('/{tenant}/work-orders/#main');
        }
		

		//zjistíme jméno a barvu týmu
		$team = (new TeamModel())->find($task['team_id']);
        if ($team === null) {
            LoggerHolder::get()->error('Task references non-existent team', [
                'task_id' => $task['id'],
                'team_id' => $task['team_id'],
                'inputTask'   => serialize($task),
                'inputOrder'  => serialize($order),
            ]);

            Flash::error('Došlo k interní chybě aplikace.');
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
        if ($order === null) {
            Flash::error('Zakázka neexistuje');
            Url::redirect('/{tenant}/work-orders/#main');
        }
		
		//zjistíme jméno a barvu týmu
        $team = (new TeamModel())->find($task['team_id']);
        if ($team === null) {
            LoggerHolder::get()->error('Task references non-existent team', [
                'task_id' => $task['id'],
                'team_id' => $task['team_id'],
                'inputTask'   => serialize($task),
                'inputOrder'  => serialize($order),
            ]);

            Flash::error('Došlo k interní chybě aplikace.');
            Url::redirect('/{tenant}/tasks/#main');
        }
        $post = $_POST;
        //nelze změnit tým, takže pro validaci 
        //musíme nastavit původní team_id
        $post['team_id'] = $task['team_id'];
        
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

            $this->addError('global', 'Litujeme, úkol se nepodařilo upravit, zkuste to prosím později znovu.');
            return $this->render('tasks/edit');
        }

	    Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#taskId_' . $taskId);
  }

    public function cloneTaskGet(int $taskId): string
    {
        $task = $this->getTaskOrRedirect($taskId);
        // $this->ensureTaskEditable($task);

        $order = (new WorkOrderModel())->find($task['work_order_id']);
        if ($order === null) {
            Flash::error('Zakázka neexistuje.');
            Url::redirect('/{tenant}/work-orders/#main');
        }

        //if (!in_array($order['status'], ['new', 'in_progress'], true)) {
        if(WOStatus::isClosed($order['status'])) {

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
        if ($order === null) {
            Flash::error('Zakázka neexistuje.');
            Url::redirect('/{tenant}/work-orders/#main');
        }

        //if (!in_array($order['status'], ['new', 'in_progress'], true)) {
        if(WOStatus::isClosed($order['status'])) {
            Flash::error('Zakázka je uzavřená, nelze klonovat.');
            Url::redirect('/{tenant}/work-orders/' . $order['id'] . '/detail/#main');
        }
    
        return $this->saveTask($_POST, $task['work_order_id']);
    }

/** 
 * @param array<string, mixed> $data 
 * @return array<string, mixed> 
 */
    private function validateTask(array $data): array
    {
        $title           = trim($data['title'] ?? '');
        $description     = trim($data['description'] ?? '');
        $is_recurring_master    = (int) ($data['is_recurring_master'] ?? 0);
        $team_id         = ($data['team_id'] ?? 0);
        $dueDate         = DateHelper::parseDate($data['due_date'] ?? null);
        
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
            'is_recurring_master' => $is_recurring_master,
            'team_id' => $team_id,
            'due_date' => $dueDate,
        ];
    }




    public function done(int $taskId): void
    {
        $task = $this->getTaskOrRedirect($taskId);
        $this->ensureTaskClosable($task, TaskStatus::DONE);

        try {

            $ok = (new TaskModel())->closeTask($taskId, TaskStatus::DONE);

            if (!$ok) {
                Flash::error('Nepodařilo se uzavřít úkol.');
                Url::back();
            }

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
        $this->ensureTaskClosable($task, TaskStatus::CANCELLED);

        try {
            $ok = (new TaskModel())->closeTask($taskId, TaskStatus::CANCELLED);
            if (!$ok) {
                Flash::error('Nepodařilo se zrušit úkol.');
                Url::back();
            }

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
        // ověříme právo na přidání reportu
        $this->checkCsrf();
        $data = $_POST;

        $canUserAddReport = self::canUserAddReport($taskId);

        if (
            !$canUserAddReport
            && !Roles::isManagement(Auth::role())
        ) {
            Flash::error('Nemáte oprávnění. Nejste členem týmu, který má úkol plnit...');
            Url::redirect('/{tenant}/tasks/#main');
        }

        $data['canUserAddReport'] = $canUserAddReport;

        // 1. Načti úkol
        $taskModel = new TaskModel();
        $task = $taskModel->find($taskId);

        if ($task === null) {
            Flash::error('Úkol neexistuje');
            Url::redirect('/{tenant}/tasks/#main');
        }

        if (TaskStatus::isClosed($task['status'])) {
            Flash::error('K tomuto úkolu již nelze přidat report.');
            Url::redirect(
                '/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main'
            );
        }

        // 2. Data reportu
        $report       = trim($data['report']);
        $kilometers   = $data['kilometers'];
        $participants = $data['participants'];
    
        // 3. Validace reportu
        if ($report === '') {
            $this->addError('report', 'Text reportu je povinný');
        }

        // 4. Validace kilometrů
        if ($kilometers < -9999 || $kilometers > 9999) {
            $this->addError(
                'kilometers',
                'Kilometry musí být v rozmezí -9999 až 9999'
            );
        }

        // 5. Aktuální členové týmu úkolu
        $teamMembership = new TeamMembership();
        $teamMembers = $teamMembership->currentMembers(
            $task['team_id']
        );

        $allowedUserIds = array_column($teamMembers, 'id');

        // 6. Validace účastníků
        foreach ($participants as $userId => &$participantData) {
            $userId = $userId;

            // Nezaškrtnutý pracovník nás nezajímá.
            if (($participantData['selected'] ?? null) !== 'on') {
                continue;
            }

            // Zaškrtnutý pracovník musí být aktuálním členem týmu úkolu.
            if (!in_array($userId, $allowedUserIds, true)) {
                $this->addError(
                    "participants.$userId",
                    'Vybraný pracovník není členem týmu tohoto úkolu.'
                );
                continue;
            }

            $sign = $participantData['sign'] ?? '+';
            $hours = trim($participantData['hours'] ?? '');
            $minutes = trim($participantData['minutes'] ?? '');
            $error  = false;

            if ($hours === '' && $minutes === '') {
                $this->addError(
                    "participants.$userId",
                    'Vyplň čas nebo odškrtni pracovníka'
                );
                continue;
            }

            if ($sign !== '+' && $sign !== '-') {
                /** zkusíme to udělat blbuvzdorné, to znasmená, dáš blbost, dostaneš + 
                 *  tím pádem není třeba hlášku
                */
                $sign = '+';
                
                /* 
                $this->addError(
                    "participants.$userId",
                    'Neplatné znaménko času.'
                );
                $error  = true;*/
            }

            if ( $hours !== '' && (!ctype_digit($hours) || $hours > 24)) {                
                $this->addError(
                    "participants.$userId",
                    'Hodiny musí být v rozmezí 0 až 24'
                );
                $error  = true;
            }

            if ($minutes !== '' && (!ctype_digit($minutes) || $minutes > 59)
            ) {
                $this->addError(
                    "participants.$userId",
                    'Minuty musí být v rozmezí 0 až 59'
                );
                $error  = true;
            }


            // jdeme přepočítat na minuty a případně udělat číslo záporné
            if($error === false) {
                $totalMinutes = (int) $hours * 60 + (int) $minutes;

                if ($totalMinutes === 0) {
                    $this->addError(
                        "participants.$userId",
                        'Čas nesmí být nulový. Zadejte alespoň 1 minutu.'
                    );
                    $error = true;

                } elseif($totalMinutes > 24 * 60) {
                        $this->addError(
                            "participants.$userId",
                            'Čas nesmí být větší než 24 hodin.'
                        );
                        $error = true;
                } else {
                    if ($sign === '-') {
                        $totalMinutes *= -1;
                    }

                    $participantData['minutes_spent'] = $totalMinutes;
                }

            }

        }
        unset($participantData);
        $data['participants'] = $participants;

        // 7. Pokud jsou chyby, vrať se zpět
        if ($this->hasErrors()) {
            $this->view->old = $data;

            return $this->addTaskReport($taskId);
        }

        // 8. Uložení reportu i s účastníky
        $assignmentModel = new TaskAssignmentModel();

        $assignmentId = $assignmentModel->createReport(
            $taskId,
            $task['work_order_id'],
            $data
        );

        if ($assignmentId > 0) {
            Flash::success('Report byl úspěšně uložen');
        } else {
            $this->addError('global', 'Nepodařilo se uložit report');
            $this->view->data = $data;

            return $this->addTaskReport($taskId);
        }

        // podařilo se uložit, redirect pod formulář
        Url::redirect('/{tenant}/tasks/' . $taskId . '/add-report/#report_list');
    }



    public function addTaskReport(int $taskId): string
    {
        // Získáme data z db
        $taskModel = new TaskModel();
        $task = $taskModel->find($taskId);
        
        if ($task === null) {
            Flash::error('Úkol neexistuje');
            Url::redirect('/{tenant}/tasks/#main');
        }
/* */
        if(TaskStatus::isClosed($task['status'])) {
            Flash::error('K tomuto úkolu již nelze přidat report.');
            Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main');
        }
/* */
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
        $assignmentModel = new TaskAssignmentModel();
        $reports = $assignmentModel->findByTask($taskId);
        
        $this->view->task = $task;
        $this->view->teamMembers = $teamMembers;
        $this->view->reports = $reports;
        
        // Pokud máme stará data z POST (při chybě), předáme je do view
        if ($this->view->old !== []) {
            $this->view->oldData = $this->view->old;
        }       
        return $this->render('tasks/report');
    }

    /** 
     * @return bool 
     */ 
    private function canUserAddReport(int $taskId): bool
    {
        //zízkáme id aktuálního přihlášeného uživatele
        $userId = Auth::id();

        //zjistíme, jestli má právo přidat report k tomuto úkolu
        if((new TaskModel())->canUserAddReport($taskId, (int) $userId) === true)
        {
            return true;
        }
    return false;
    }

}