<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Core\viewContext;
use App\Core\Url;
use App\Models\WorkOrderModel;
use App\Models\TaskModel;
use App\Models\Team;

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
    $this->view->orders = $this->model->all();
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
				$this->addError('global', 'Zakázku se nepodařilo vytvořit.');
            return $this->render('work_orders/create');
			
			}
        Flash::success('Zakázka byla úspěšně vytvořena.');

        Url::redirect('/work-orders/' . $orderId . '/detail');
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
        Url::redirect('/work-orders/' . $orderId . '/detail');
    }
    
    public function detail(int $orderId): string
{
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		//ověříme data v pomocné metodě
		$post = $this->createTask($orderId);//array
	}
    $order = $this->getOrderOrRedirect($orderId);

    $taskModel = new TaskModel();
    $tasks = $taskModel->forWorkOrderWithStats($orderId);

    $teamModel = new Team();
    $teams = $teamModel->byActive(true);

    $this->view->order = $order;
    $this->view->tasks = $tasks;
    $this->view->teams = $teams;

    // data formuláře (pro sticky input / chyby)
    $this->view->taskFormData   = $this->view->taskFormData   ?? [];
    $this->view->taskFormErrors = $this->view->taskFormErrors ?? [];

    return $this->render('work_orders/detail');
}

//metoda ověří POST data, provede uložení a vrátí array
private function createTask(int $orderId): array
{
    $this->checkCsrf();
    
    $data = [
        'work_order_id' => $orderId,
        'team_id'       => (int)($_POST['team_id'] ?? 0),
        'title'         => trim($_POST['title'] ?? ''),
        'description'   => trim($_POST['description'] ?? ''),
        'is_urgent' 		=> (int) ($_POST['is_urgent'] ?? 0),
        'created_by_user_id' => Auth::id(),
    ];
	//ověření existence týmu
    $teamModel = new Team();
    $team = $teamModel->find($data['team_id']);
        if (!$team) {
            $this->addErrors('team_id', 'Tým neexistuje.');
        }
	//ověření povinného názvu úkolu
    if ($data['title'] === '') 
    {
			$this->addErrors('title','Název zakázky je povinný.');
        
    } elseif(mb_strlen($data['title'] ) >= 255 )
    {
			$this->addErrors('title','Název úkolu je příliš dlouhý.');
    }
    
    //Ověření max délky description 10000 znaků
    if(mb_strlen($data['description'] ) >= 10000 )
    {
			$this->addErrors('description','Popis úkolu je příliš dlouhý.');
    }
    $data['is_urgent'] = $data['is_urgent'] === 1 ? 1 : 0;
    
    if($this->hasErrors()) {
    		$data['ok'] = false;
    		return $data;
    }
    $taskModel = new TaskModel();
    $data['task_id'] = $taskModel->create($data);
    if(is_int($data['task_id']) ) {
    	$data['ok'] = true;
    } else {
    	$data['ok'] = false;
    }
    return $data;
}



public function detailold(int $orderId)
{
    $order = $this->getOrderOrRedirect($orderId);

    $taskModel = new TaskModel();
    $tasks = $taskModel->forWorkOrderWithStats($orderId);

    $this->view->order = $order;
    $this->view->tasks = $tasks;

    return $this->render('work_orders/detail');
}
    /* ==========================
       PRIVATE HELPERS
       ========================== */

private function getOrderOrRedirect(int $orderId): array
{
    if ($orderId <= 0) {
        Url::redirect('/work-orders');
    }

    $order = $this->model->find($orderId);

    if (!$order) {
        Flash::error('Zakázka neexistuje');
        Url::redirect('/work-orders');
    }

    return $order;
}

    private function validate(array $post): array
    {
        $this->checkCsrf();
  
  
  //`status` enum('new','in_progress','done','exported','cancelled') NOT NULL DEFAULT 'new',
  //`created_by_user_id` bigint(20) UNSIGNED NOT NULL,
  //`created_at` datetime NOT NULL DEFAULT current_timestamp(),
  //`closed_at` datetime DEFAULT NULL
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
 public function closeCanceled(int $orderId)
{
    $taskModel = new TaskModel();
    $woModel   = new WorkOrderModel();

    $stats = $taskModel->statsForWorkOrder($orderId);

    if ($stats['open'] > 0 || $stats['done'] > 0) {
        $this->flashError(
            'Zakázku nelze zrušit, protože obsahuje otevřené nebo dokončené úkoly.'
        );
        return $this->redirect("/work-orders/{$orderId}#main");
    }

    $woModel->update($orderId, [
        'status'    => 'cancelled',
        'closed_at'=> date('Y-m-d H:i:s'),
    ]);

    $this->flashSuccess('Zakázka byla zrušena.');
    return $this->redirect('/work-orders#main');
}

public function closeDone(int $orderId)
{
    $taskModel = new TaskModel();
    $woModel   = new WorkOrderModel();

    $stats = $taskModel->statsForWorkOrder($orderId);

    if ($stats['open'] > 0) {
        $this->flashError(
            'Zakázku nelze dokončit, dokud existují otevřené úkoly.'
        );
        return $this->redirect("/work-orders/{$orderId}#main");
    }

    if ($stats['done'] === 0) {
        $this->flashError(
            'Zakázku nelze dokončit, protože nemá žádný dokončený úkol.'
        );
        return $this->redirect("/work-orders/{$orderId}#main");
    }

    $woModel->update($orderId, [
        'status'    => 'done',
        'closed_at'=> date('Y-m-d H:i:s'),
    ]);

    $this->flashSuccess('Zakázka byla dokončena.');
    return $this->redirect('/work-orders#main');
}

public function closeTaskDone(int $orderId, int $taskId): void
{
    $taskModel = new TaskModel();

    if (!$taskModel->belongsToOrder($taskId, $orderId)) {
        throw new LogicException('Neplatný kontext úkolu');
    }

    $taskModel->markDone($taskId);

    $this->recomputeOrder($orderId);

    Redirect::back();
}

private function recomputeOrder(int $orderId): void
{
    $workOrderModel = new WorkOrderModel();
    $workOrderModel->recomputeStatus($orderId);
}

}
