<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\WorkOrderModel;
use App\Core\Url;
use App\Core\Flash;

class WorkOrderController extends Controller
{
    public function index(): string
    {
        $model = new WorkOrderModel();
        $this->view->orders = $model->all();

        return $this->render('work_orders/index');
    }

    public function createForm(): string
    {
        $this->view->data = [];
        $this->view->errors = [];

        return $this->render('work_orders/create');
    }

    public function create(): string
    {
    	 $data = $this->validate($_POST);
			
        if ($this->hasErrors()) {
            $this->view->data   = $data;
            return $this->render('work_orders/create');
        }

        $model = new WorkOrderModel();
        $id = $model->create($data);
        if($model->update($id, $data))
        {
				Flash::add('succes', 'Zakázka ' . $data['name'] . ' byla uspěšně vytvořena.' );     
        } else {
				Flash::add('error', 'Zakázku ' . $data['name'] . ' se nepodařilo vytvořit.' ) ;    
        	
        }
        
        Url::redirect('/work-orders/' . $id);
    }

    public function update(int $id): string
    {

        $model = new WorkOrderModel();
        $order = $model->find($id);
        if (!$order) {
        		Flash::add('error', 'Zakázka neexistuje');
            Url::redirect('/work-orders');
        }
        //$order['path'] = 'work_orders/update';
        // pokud jdeme getem, načteme data puvodní z databáze
        // a pošleme je do formuláře
			if ($_SERVER['REQUEST_METHOD'] === 'GET') {
				$this->view->data   = $order;
				return $this->render('work_orders/create');
			}
			
			// takže jdeme postem, valiace dat:
			$data = $this->validate($_POST);
			if ($this->hasErrors()) {
				$this->view->data   = $data;
				return $this->render('work_orders/create');
			}

        $model = new WorkOrderModel();
        if($model->update($id, $data))
        {
				Flash::add('succes', 'Zakázka ' . $data['name'] . ' byla uspěšně změněna.' );     
        } else {
				Flash::add('error', 'Zakázku ' . $data['name'] . ' se nepodařilo změnit.' ) ;    
        	
        }
        

        Url::redirect('/work-orders/' . $id);
    }

    public function detail(int $id): string
    {
        $model = new WorkOrderModel();
        $order = $model->find($id);
        if (!$order) {
            Url::redirect('/work-orders');
        }

        $this->view->order = $order;
        return $this->render('work_orders/detail');
    }
    
    private function validate(array $post): array
    {

		        $data = [
            'external_number' => trim($post['external_number'] ?? null),
            'title'           => trim($post['title'] ?? ''),
            'description'     => trim($post['description'] ?? ''),
            'source'          => $post['source'] ?? 'personal',
            'requested_by'    => trim($post['requested_by'] ?? ''),
            'contact'    => trim($post['contact'] ?? ''),
            'priority'        => $post['priority'] ?? 'normal',
            'user_id'         => Auth::user()['id'],
            
        ];
        $this->checkCsrf();
			
        if ($data['external_number'] === '') {
            $data['external_number'] = null;
        }
			if ($data['title'] === '') {
				$this->addError('title', 'Název je povinný');
        }

			return $data;
    }
}
