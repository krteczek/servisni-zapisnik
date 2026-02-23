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

    public function createformStore(): string
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

    public function editForm(int $orderId): string
    {
        $order = $this->getOrderOrRedirect($orderId);

        $this->view->data = $order;
        return $this->render('work_orders/create');
    }

    public function editFormUpdate(int $orderId): string
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


}
