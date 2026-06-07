<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Core\ViewContext;
use App\Core\Url;
use App\Core\LoggerHolder;
use App\Core\Database;
use App\Models\WorkOrderModel;
use App\Models\TaskModel;
use App\Models\TeamModel;
use App\Models\ContactsModel;
use App\Services\WorkOrders\WorkOrderNumberService;
use PDO;
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
        $contactsModel = new ContactsModel();
    	$this->view->contacts = $contactsModel->all();


        $this->view->data     = [];
        $this->view->errors   = [];
        $Tm = new TeamModel();
        $this->view->data["is_edit"] = false;

        $this->view->teams = $Tm->byActive(true);         

        return $this->render('work_orders/create');
    }

    public function createFormStore(): string
    {
        $post = $_POST;
        
        $data = $this->validate($post, false);
        $contactsModel = new ContactsModel();
        $data["is_edit"] = false; 
        
        
        if ($this->hasErrors()) {
            
            $this->view->data = $data;
            $this->view->contacts = $contactsModel->all();
            $Tm = new TeamModel();
            $this->view->teams = $Tm->byActive(true);         

            return $this->render('work_orders/create');
        }
        
        $pdo = null;
        try {
            $toDb = [
                'contact_id'         => $data['contact_id'],
                'price_per_hour'     => $data['price_per_hour'],
                'price_per_km'       => $data['price_per_km'],
                'external_number' 	 => $data['external_number'],
                'title'           	 => $data['title'],
                'description'     	 => $data['description'],
                'source'          	 => $data['source'],
                'requested_by'    	 => $data['requested_by'],
                'contact_person'     => $data['contact_person'],
                'priority'        	 => $data['priority'],
                'created_by_user_id' => $data['created_by_user_id'],
                'wo_due_date'        => $data['wo_due_date'],
                'estimated_hours'    => $data['estimated_hours'],
            ];
            
            
			    $pdo = Database::work();
			    $pdo->beginTransaction();

		
                $WONS = new WorkOrderNumberService();
                $orderId = $WONS->generateAndCreate($toDb, $pdo);

                // vytvoříme první úkol, pokud je to požadováno
                /* změna plánu, vytvoření nového tasku bude tsk, že se do formuláře úkolu vkopírují data ze zakázky 
                * a uživatel rozhodne, jakým způsobem bude pokračovat (třeba vytvořením opakovaného úkolu)
                * * /
                if ($data['create_first_task'] === true) {
                    $taskModel = new TaskModel();
                    $taskModel->setConnection($pdo);
                    $taskModel->create([
                        'work_order_id' => $orderId,
                        'title'         => $data['title'], // můžeme použít název zakázky jako název úkolu
                        'description'   => $data['description'], // můžeme použít popis zakázky jako popis úkolu
                        'status'        => 'open',
                        'team_id'       => $data['team_id'],
                        'created_by_user_id' => $data['created_by_user_id'],
                    ]);

                    $this->model->setConnection($pdo);

                    $this->model->update($orderId, [
                                    'status' => 'in_progress',
                    ]);
                    
               }*/

               $pdo->commit();
		} catch (\Throwable $e) {
			if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            
		    LoggerHolder::get()->error('WorkOrderController.createFormStore: failed', [
		                'message' => $e->getMessage(),
		                'file'    => $e->getFile(),
		                'line'    => $e->getLine(),
		                'trace'   => $e->getTraceAsString(),
		                

		    ]);
 
            $this->addError('global', 'Zakázku se nepodařilo vytvořit.');
            $Tm = new TeamModel();
            $this->view->teams = $Tm->byActive(true);
            $this->view->data = $data;
            $this->view->contacts = $contactsModel->all();

            return $this->render('work_orders/create');
		}
        Flash::success('Zakázka byla úspěšně vytvořena.');
        if ($data['create_first_task'] === true) {
            Url::redirect('/{tenant}/work-orders/' . $orderId . '/tasks/createTaskFromOrderGet/');
        }
        Url::redirect('/{tenant}/work-orders/' . $orderId . '/detail');
    }

    private function guardEditable(array $order, int $orderId): void
    {
        if ($order['status'] === 'cancelled') {
            Flash::error('Tuto zakázku nelze upravovat, protože je zrušená.');
            Url::redirect('/{tenant}/work-orders/' . $orderId . '/detail');
        }

        if ($order['status'] === 'done') {
            Flash::error('Tuto zakázku nelze upravovat, protože je dokončená.');
            Url::redirect('/{tenant}/work-orders/' . $orderId . '/detail');
        }
    }

    public function editForm(int $orderId): string
    {
        $order = $this->getOrderOrRedirect($orderId);
        $this->guardEditable($order, $orderId);

        $TaM = new TaskModel();
        $stats = $TaM->statsForWorkOrder($orderId);
        $order['is_edit'] = true; // pro případné úpravy ve view ,
        $order['task_count'] = $stats['total'] ?? 0; // počet úkolů pro zobrazení upozornění ve view, že nelze vytvořit první úkol ze zakázky, protože již nějaké úkoly existují
        
        $contactsModel = new ContactsModel();
        $TM = new TeamModel();

        $this->view->teams = $TM->byActive(true);
        $this->view->data = $order;
        
        $this->view->contacts = $contactsModel->all(); // 🔥 DŮLEŽITÉ

        return $this->render('work_orders/create');
    }

    public function editFormUpdate(int $orderId): string
    {
        $order = $this->getOrderOrRedirect($orderId);
        $this->guardEditable($order, $orderId);

        $post = $_POST;
        

        $TaM = new TaskModel();
        $stats = $TaM->statsForWorkOrder($orderId);
        $data = $this->validate($post, true);
        $data["is_edit"] = true;
        $data['task_count'] = $stats['total'] ?? 0; // počet úkolů pro zobrazení upozornění ve view, že nelze vytvořit první úkol ze zakázky, protože již nějaké úkoly existují

        if ($this->hasErrors()) {
            $contactsModel = new ContactsModel();
            $TM = new TeamModel();
            $this->view->teams = $TM->byActive(true);
            $this->view->contacts = $contactsModel->all();
            $this->view->data = $data;
            return $this->render('work_orders/create');
            
        }
        
        try {
            $toDb = [
                'contact_id'         => $data['contact_id'],
                'price_per_hour'     => $data['price_per_hour'],
                'price_per_km'       => $data['price_per_km'],
                'external_number' 	 => $data['external_number'],
                'title'           	 => $data['title'],
                'description'     	 => $data['description'],
                'source'          	 => $data['source'],
                'requested_by'    	 => $data['requested_by'],
                'contact_person'     => $data['contact_person'],
                'priority'        	 => $data['priority'],
                'wo_due_date'        => $data['wo_due_date'],
                'estimated_hours'    => $data['estimated_hours'],
            ];
            $ok = $this->model->update($orderId, $toDb);
                
            
                
            Flash::success('Zakázka byla úspěšně změněna.');
            Url::redirect('/{tenant}/work-orders/' . $orderId . '/detail');
        } catch (\Throwable $e) {
            
            LoggerHolder::get()->error('WorkOrderController.editFormUpdate: failed', [
                        'message' => $e->getMessage(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                        'trace'   => $e->getTraceAsString(),
            ]);
        }

        $TM = new TeamModel();
        $this->view->teams = $TM->byActive(true);        
        $contactsModel = new ContactsModel();
        $this->view->contacts = $contactsModel->all();
        $this->addError('global','Zakázku se nepodařilo změnit.');
        $this->view->data = $data;
        return $this->render('work_orders/create');
              
    }
    

public function detailOrder(int $orderId): string
{
    $order = $this->getOrderOrRedirect($orderId);

    $taskModel = new TaskModel();
    $tasks = $taskModel->forWorkOrderWithStats($orderId);

    $teamModel = new TeamModel();
    $teams = $teamModel->byActive(true);

    if((int)$order['contact_id'] > 0)
    {
        $contactsModel = new ContactsModel();
        $contact = $contactsModel->find($order['contact_id']);
    } else {
        $contact = ['company_name' => '—'];
    }

    

    // 1. Vytvoř lookup mapu týmů (id => [name, color])
    $teamMap = [];
    foreach ($teams as $team) {
        $teamMap[$team['id']] = [
            'team_name'  => $team['name']   ?? '—',
            'team_color' => $team['color']  ?? '#cccccc',
        ];
    }

    // 2. Inicializace součtů zakázky
    $totalMinutes       = 0;
    $totalKm            = 0;
    $reportCount        = 0;
    $openTaskCount      = 0;
    $doneTaskCount      = 0;
    $cancelledTaskCount = 0;

    // 3. Rozšíření úkolů o tým + výpočet součtů
    foreach ($tasks as &$task) {
        $teamId = $task['team_id'] ?? null;

        // Přidání týmu (s fallbackem)
        if ($teamId !== null && isset($teamMap[$teamId])) {
            $task['team_name']  = $teamMap[$teamId]['team_name'];
            $task['team_color'] = $teamMap[$teamId]['team_color'];
        } else {
            $task['team_name']  = '—';
            $task['team_color'] = '#cccccc'; // šedá default
        }

        // Bezpečné sčítání statistik (ochrana před chybějícími klíči)
        $stats = $task['stats'] ?? [];
        $totalMinutes += (int) ($stats['total_minutes'] ?? 0);
        $totalKm      += (int) ($stats['total_km']      ?? 0);
        $reportCount  += (int) ($stats['assignments_count'] ?? 0);
        if ($task['status'] === 'open')
        {
            $openTaskCount++;
        }
        elseif ($task['status'] === 'done')
        {
            $doneTaskCount++;
        }
        elseif ($task['status'] === 'cancelled')
        {
            $cancelledTaskCount++;
        }

    }
    unset($task); // dobrý zvyk po použití reference

    // 4. Formátování celkového času (vždycky hezky "X h Y min")
    $hours = floor($totalMinutes / 60);
    $mins  = $totalMinutes % 60;
    $order['total_time']            = $hours . ' h ' . $mins . ' min'; // lepší název než jen 'hours'
    $order['total_km']              = $totalKm;
    $order['report_count']          = $reportCount;
    $order['total_tasks_count']     = count($tasks);
    $order['open_tasks_count']      = $openTaskCount;
    $order['done_tasks_count']      = $doneTaskCount;
    $order['cancelled_tasks_count'] = $cancelledTaskCount;
    $order['company_name']          = $contact['company_name'];
    
        // příznak pro zobrazení odkazu na dokončení zakázky, pokud jsou všechny 
    // úkoly hotové nebo zrušené, nebo pokud nejsou žádné úkoly
    $order['ready_for_done'] = false;

    if(((int)$order['total_tasks_count'] > 0) &&     
            (((int)$order['done_tasks_count'] + (int)$order['cancelled_tasks_count']) === (int)$order['total_tasks_count'])) 
    {
                //odkaz na uzavření zakázky, pokud jsou všechny úkoly hotové nebo zrušené 
                //nebo pokud nejsou žádné úkoly
                if($order['status'] <> 'done') {
                    $order['ready_for_done'] = true;
                }
    }

    $order['ready_for_cancel'] = false;
    if(((int)$order['total_tasks_count'] === 0) ||
        ((int)$order['total_tasks_count'] === (int)$order['cancelled_tasks_count']))
    {
        //odkaz na zrušení zakázky, pokud jsou všechny úkoly zrušené
        $order['ready_for_cancel'] = true;
    }

    // 5. Předání do view
    $this->view->order = $order;
    $this->view->tasks = $tasks;
    //$this->view->teams = $teamMap;  // nepotřebuješ, pokud ho nepoužíváš ve view

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

    private function validate(array $post, bool $isEdit = false): array
    {
        $this->checkCsrf();
        $data = [

            'contact_id'         => (int)($post['contact_id'] ?? 0) ?: null,
            'price_per_hour'     => (int)($post['price_per_hour'] ?? 0),
            'price_per_km'       => (int)($post['price_per_km'] ?? 0),
            'external_number' 	 => trim($post['external_number'] ?? '') ?: null,
            'title'           	 => trim($post['title'] ?? ''),
            'description'     	 => trim($post['description'] ?? ''),
            'source'          	 => $post['source'] ?? 'personal',
            'requested_by'    	 => trim($post['requested_by'] ?? ''),
            'contact_person'     => trim($post['contact_person'] ?? ''),
            'priority'        	 => $post['priority'] ?? 'normal',
            'created_by_user_id' => Auth::id(),
            'create_first_task'  => !empty($post['create_first_task']),
            'team_id'            => isset($post['team_id']) ? (int)$post['team_id'] : 0, 
            'wo_due_date'        => isset($post['wo_due_date']) ? $post['wo_due_date'] : null,
            'estimated_hours'    => isset($post['estimated_hours']) ? (int)$post['estimated_hours'] : null,
        ];
		//   `title` varchar(255) NOT NULL,
		$this->maxLength('title', $data['title'], 255, 'Název zakázky');
		if ($data['title'] === '') {
			$this->addError('title', 'Název zakázky je povinný');
		} 
        //  `external_number` varchar(100) DEFAULT NULL,
        $this->maxLength('external_number', $data['external_number'], 100, 'Externí číslo zakázky');
        
        //   `description` text DEFAULT NULL, max 65 535 znaků omezíme na 10000
        $this->maxLength('description', $data['description'], 10000, 'Popis zakázky');
        
        // `contact` varchar(255) DEFAULT NULL,
        $this->maxLength('contact_person', $data['contact_person'], 255, 'Kontakt');
                    
        // `source` enum('email','phone','personal','system') NOT NULL,
        $sources = ['email','phone','personal','system'];
        if (!in_array($data['source'], $sources, true)) {
            $data['source'] = 'personal';
        }

        //   `requested_by` varchar(255) DEFAULT NULL,
        $this->maxLength('requested_by', $data['requested_by'], 255, 'Požadoval');
        
        //`priority` enum('low','normal','high','emergency') NOT NULL DEFAULT 'normal',
        $priorities = ['low','normal','high','emergency'];
        if (!in_array($data['priority'], $priorities, true)) {
            $data['priority'] = 'normal';
        }

        if($data['price_per_hour'] < 0)
        {
            $this->addError('price_per_hour', 'Hodinová sazba nemůže být záporná.');
        }
        if($data['price_per_km'] < 0)
        {
            $this->addError('price_per_km', 'Kilometrová sazba nemůže být záporná.');
        }

        $data['wo_due_date'] = $this->validateDate($data['wo_due_date']);
        if ($data['wo_due_date'] === null && isset($post['wo_due_date']) && trim($post['wo_due_date']) !== '') {
            $this->addError('wo_due_date', 'Neplatný formát data. Použijte formát RRRR-MM-DD.');
        }

        if ($data['estimated_hours'] !== null && $data['estimated_hours'] < 0) {
            $this->addError('estimated_hours', 'Odhadované hodiny nemohou být záporné.');
        }

        if ($data["create_first_task"] === true && $isEdit === false)
        {
            if($data['team_id'] === 0) {
                $this->addError('team_id', 'Pro vytvoření prvního úkolu je nutné vybrat tým.');
            } else {
                // Ověříme, že zadané team_id skutečně existuje
                $teamModel = new TeamModel();
                $team = $teamModel->find($data['team_id']);
                if (!$team) {
                    $this->addError('team_id', 'Vybraný tým neexistuje.');
                } else {
                    $data['team_id'] = (int)$team['id'];
                }
            }
        }
       
        return $data;
    }
  
private function validateDate(?string $date): ?string
{
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

public function closeOrderCanceled(int $orderId)
{   
    $order = $this->getOrderOrRedirect($orderId);
    $this->guardEditable($order, $orderId);
    try {
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

        } catch (\Throwable $e) {
            LoggerHolder::get()->error('WorkOrderController.closeOrderCanceled: failed', [
                        'message' => $e->getMessage(),
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                        'trace'   => $e->getTraceAsString(),
            ]);
            Flash::error('Zakázku se nepodařilo zrušit.');
            Url::redirect('/{tenant}/work-orders/' . $orderId . '/detail/#main');
        }
    
}

public function closeOrderDone(int $orderId)
{
    $order = $this->getOrderOrRedirect($orderId);
    $this->guardEditable($order, $orderId);
    try{
        $woModel = new WorkOrderModel();
        $ok = $woModel->closeAsDone($orderId);
        if($ok === false)
        {
            Flash::error('Zakázku se nepodařilo dokončit.');
            Url::redirect('/{tenant}/work-orders/' . $orderId . '/detail/#main');
        }
        Flash::success('Zakázka byla dokončena.');
        Url::redirect('/{tenant}/work-orders/' . $orderId . '/detail/#main');

    } catch (\Throwable $e) {
        LoggerHolder::get()->error('WorkOrderController.closeOrderDone: failed', [
                    'message' => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                    'trace'   => $e->getTraceAsString(),
        ]);
        Flash::error('Zakázku se nepodařilo dokončit.');
        Url::redirect('/{tenant}/work-orders/' . $orderId . '/detail/#main');
    }
 

}

}
