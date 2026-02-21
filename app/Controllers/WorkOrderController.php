<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Core\ViewContext;
use App\Core\Url;
use App\Core\Logger;
use App\Models\WorkOrderModel;
use App\Models\TaskModel;
use App\Models\TeamModel;

class WorkOrderController extends Controller
{
    private WorkOrderModel $model;

    public function __construct(ViewContext $view)
    {
        parent::__construct($view);
        $this->model = new WorkOrderModel();
    }
public function index(): string
{	
	
    $this->view->orders = $this->model->forIndex();
    return $this->render('work_orders/index');
}

    public function createForm(): string
    {
        $this->view->data   = [];
        $this->view->errors = [];

        return $this->render('work_orders/create');
    }

    public function create(): string
    {
        $data = $this->validate($_POST);

        if ($this->hasErrors()) {
            $this->view->data = $data;
            return $this->render('work_orders/create');
        }

        $orderId = $this->model->create($data);
        $this->model->recomputeStatus($orderId);
			if(!$orderId) {
				$this->addErrors('global', 'Zakázku se nepodařilo vytvořit.');
            return $this->render('work_orders/create');
			
			}
        Flash::success('Zakázka byla úspěšně vytvořena.');

        Url::redirect('/{tenant}/work-orders/' . $orderId . '/detail');
    }

    public function edit(int $orderId): string
    {
        $order = $this->getOrderOrRedirect($orderId);

        $this->view->data = $order;
        return $this->render('work_orders/create');
    }

    public function update(int $orderId): string
    {
        $this->getOrderOrRedirect($orderId);

        $data = $this->validate($_POST);

        if ($this->hasErrors()) {
            $this->view->data = $data;
            return $this->render('work_orders/create');
            
        }
			$ok = $this->model->update($orderId, $data);
			$add = $ok ? 'success' : 'error';
			if(!$ok){
				$this->addErrors('global','Zakázku se nepodařilo změnit.');
				$this->view->data = $data;
				return $this->render('work_orders/create');
			}
			
        Flash::success('Zakázka byla úspěšně změněna.');
        Url::redirect('/{tenant}/work-orders/' . $orderId . '/detail');
    }
	private function setViewForDetail(int $orderId): void
	{
	    $order = $this->getOrderOrRedirect($orderId);
	
	    $taskModel = new TaskModel();
	    $tasks = $taskModel->forWorkOrderWithStats($orderId);
	
	    $teamModel = new TeamModel();
	    $teams = $teamModel->byActive(true);
	
	    $this->view->order = $order;
	    $this->view->tasks = $tasks;
	    $this->view->teams = $teams;
	    
		
	    // data formuláře (pro sticky input / chyby)
	    $this->view->taskFormData   = $this->view->taskFormData   ?? [];
	    $this->view->taskFormErrors = $this->view->taskFormErrors ?? [];
	
	}
    
public function detailOrder(int $orderId): string
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $result = $this->validateTask(
            ['work_order_id' => $orderId],
            'CREATE'
        );

        if ($result['ok']) {
            Flash::success('Úkol byl úspěšně vytvořen.');
            Url::redirect(
                '/{tenant}/work-orders/' 
                . $orderId 
                . '/detail/#taskId_' 
                . $result['task_id']
            );
        }

        $this->view->taskFormData = $result['data'];
    }

    $this->setViewForDetail($orderId);
    return $this->render('work_orders/detail');
}
		
		
public function detailOrderEditTask(int $orderId, int $taskId)
{	
	
	//ověříme, že existuje task
	$task = (new TaskModel())->find($taskId);

	if(!$task) {
		// neexistuje
		Flash::error('Úkol neexistuje.');
		Url::redirect('/{tenant}/tasks/#main');
	}
	$orderId = (int)$task['work_order_id'];

	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') 
	{
		//ověříme/uložíme data v pomocné metodě
		$post = $this->validateTask($task, 'UPDATE');//array
		
		if($post['ok'] === true){
			//všechno ok, redirect
        Flash::success('Úkol byl úspěšně vytvořen.');
        Url::redirect('/{tenant}/work-orders/' . $orderId . '/detail/#taskId_' . $post['task_id']);
			
		}
	}

	if ($_SERVER['REQUEST_METHOD'] === 'GET') {
		//potřebovali jsme jen načíst $task
		$post = $task;
	}
	$this->view->post = $post ?? [];
	$this->setViewForDetail($orderId);
	return $this->render('work_orders/detail');
}



	/*
		metoda ověří POST data, provede uložení a vrátí array
	*/
private function validateTask(array $data, string $method): array
{
    $this->checkCsrf();

    $workId = (int)($data['work_order_id'] ?? 0);
    $taskId = (int)($data['id'] ?? 0);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = $_POST;
    }

    $title        = trim($data['title'] ?? '');
    $description  = trim($data['description'] ?? '');
    $team_id      = (int)($data['team_id'] ?? 0);
    $work_order_id = (int)($data['work_order_id'] ?? 0);

    if ($title === '') {
        $this->addError('title', 'Název úkolu je povinný');
    }

    if (mb_strlen($title) > 250) {
        $this->addError('title', 'Název úkolu je příliš dlouhý');
    }

    if (mb_strlen($description) > 10000) {
        $this->addError('description', 'Popis úkolu je příliš dlouhý');
    }

    if (!(new TeamModel())->find($team_id)) {
        $this->addError('team_id', 'Vybraný tým neexistuje');
    }

    if (!$this->model->find($work_order_id)) {
        $this->addError('work_order_id', 'Zakázka neexistuje');
    }

    if ($this->hasErrors()) {
        return [
            'ok' => false,
            'data' => $data
        ];
    }

    $toDb = [
        'title' => $title,
        'description' => $description,
        'team_id' => $team_id,
        'work_order_id' => $work_order_id,
        'created_by_user_id' => Auth::id(),
    ];

    $taskModel = new TaskModel();

    if ($method === 'UPDATE') {
        $ok = $taskModel->update($taskId, $toDb);
        return [
            'ok' => $ok,
            'task_id' => $taskId,
            'data' => $data
        ];
    }

    if ($method === 'CREATE') {
        $newId = $taskModel->create($toDb);
        return [
            'ok' => (bool)$newId,
            'task_id' => $newId,
            'data' => $data
        ];
    }

    return ['ok' => false];
}

    /* ==========================
       PRIVATE HELPERS
       ========================== */

private function getOrderOrRedirect(int $orderId): array
{
    if ($orderId <= 0) {
        Url::redirect('/{tenant}/work-orders');
    }

    $order = $this->model->find($orderId);

    if (!$order) {
        Flash::error('Zakázka neexistuje');
        Url::redirect('/{tenant}/work-orders');
    }

    return $order;
}

    private function validate(array $post): array
    {
        $this->checkCsrf();

        $data = [
            'external_number' 	=> trim($post['external_number'] ?? '') ?: null,
            'title'           	=> trim($post['title'] ?? ''),
            'description'     	=> trim($post['description'] ?? ''),
            'source'          	=> $post['source'] ?? 'personal',
            'requested_by'    	=> trim($post['requested_by'] ?? ''),
            'contact'         	=> trim($post['contact'] ?? ''),
            'priority'        	=> $post['priority'] ?? 'normal',
            'created_by_user_id' => Auth::id(),
        ];
			//   `title` varchar(255) NOT NULL,
			$this->maxLength('title', $data['title'], 255, 'Název zakázky');
			if ($data['title'] === '') {
				$this->addError('title', 'Název zakázky je povinný');
			} 
			//  `external_number` varchar(100) DEFAULT NULL,
			$this->maxLength('external_number', $data['external_number'], 10000, 'Externí číslo zakázky');
			
			//   `description` text DEFAULT NULL, max 65 535 znaků omezíme na 10000
			$this->maxLength('description', $data['description'], 10000, 'Popis zakázky');
			
			// `contact` varchar(255) DEFAULT NULL,
			$this->maxLength('contact', $data['contact'], 255, 'Kontakt');
						
			// `source` enum('email','phone','personal','system') NOT NULL,
			$sources = ['email','phone','personal','system'];
			if (!in_array($data['source'], $sources, true)) {
				$data['source'] = 'personal';
			}

			//   `requested_by` varchar(255) DEFAULT NULL,
			$this->maxLength('requested_by', $data['requested_by'], 255, 'Požadoval');
			
			//`priority` enum('low','normal','high','emergency') NOT NULL DEFAULT 'normal',
			$prioritys = ['low','normal','high','emergency'];
			if (!in_array($data['priority'], $prioritys, true)) {
				$data['priority'] = 'normal';
			}

        return $data;
    }
    
 public function closeOrderCanceled(int $orderId)
{
    $taskModel = new TaskModel();
    $woModel   = new WorkOrderModel();

    $stats = $taskModel->statsForWorkOrder($orderId);

    if ($stats['open'] > 0 || $stats['done'] > 0) {
        Flash::error(
            'Zakázku nelze zrušit, protože obsahuje otevřené nebo dokončené úkoly.'
        );
        Url::redirect("/{tenant}/work-orders/{$orderId}/detail/#main");
    }

    $woModel->update($orderId, [
        'status'    => 'cancelled',
        'closed_at'=> date('Y-m-d H:i:s'),
    ]);

    Flash::success('Zakázka byla zrušena.');
    Url::redirect('/{tenant}/work-orders/#main');
}

public function closeOrderDone(int $orderId)
{
    $woModel = new WorkOrderModel();

    if (!$woModel->closeAsDone($orderId)) {
        Flash::error('Zakázku se nepodařilo dokončit.');
        Url::redirect('/{tenant}/work-orders/' . $orderId . '/detail/#main');
    }

    Flash::success('Zakázka byla dokončena.');
    Url::redirect('/{tenant}/work-orders/' . $orderId . '/detail/#main');
}

private function recomputeOrder(int $orderId): void
{
    $this->model->recomputeStatus($orderId);
}


public function closeTaskCanceled(int $orderId, int $taskId): void
{
    $this->closeTask($orderId, $taskId, 'canceled');
}

public function closeTaskDone(int $orderId, int $taskId): void
{
    $this->closeTask($orderId, $taskId, 'done');
}

private function closeTask(int $orderId, int $taskId, string $status): void
{
    try {
        $taskModel = new TaskModel();

        if (!$taskModel->belongsToOrder($taskId, $orderId)) {
            Flash::error('Úkol nepatří k této zakázce.');
            Url::redirect('/{tenant}/work-orders/' . $orderId);
        }

        if (!$taskModel->canBeClosed($taskId, $status)) {
            Flash::error('Úkol nelze v tomto stavu uzavřít.');
            Url::redirect('/{tenant}/work-orders/' . $orderId);
        }

        if (!$taskModel->closeTask($taskId, $status)) {
            Flash::error('Nepodařilo se uzavřít úkol.');
            Url::redirect('/{tenant}/work-orders/' . $orderId);
        }

        Flash::success('Úkol byl úspěšně uzavřen.');
        Url::redirect('/{tenant}/work-orders/' . $orderId);

    } catch (\Throwable $e) {
        Logger::error($e);
        Flash::error('Nepodařilo se uzavřít úkol.');
        Url::redirect('/{tenant}/work-orders/' . $orderId);
    }
}
}
