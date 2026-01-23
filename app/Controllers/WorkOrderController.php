<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\WorkOrderModel;
use App\Core\Url;

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
        $data = [
            'external_number' => trim($_POST['external_number'] ?? ''),
            'title'           => trim($_POST['title'] ?? ''),
            'description'     => trim($_POST['description'] ?? ''),
            'source'          => $_POST['source'] ?? 'personal',
            'requested_by'    => trim($_POST['requested_by'] ?? ''),
            'priority'        => $_POST['priority'] ?? 'normal',
            'user_id'         => Auth::user()['id'],
        ];

        $errors = [];

        if ($data['title'] === '') {
            $errors['title'][] = 'Název je povinný';
        }

        if ($errors) {
            $this->view->errors = $errors;
            $this->view->data   = $data;
            return $this->render('work_orders/create');
        }

        $model = new WorkOrderModel();
        $id = $model->create($data);

        Url::redirect('/work-orders/' . $id);
    }

    public function update($id): string
    {

        $model = new WorkOrderModel();
        $order = $model->find($id);
        if (!$order) {
        		Flash::add('error', 'Zakázka neexistuje');
            Url::redirect('/work-orders');
        }
			
			if()
        $data = [
            'external_number' => trim($_POST['external_number'] ?? ''),
            'title'           => trim($_POST['title'] ?? ''),
            'description'     => trim($_POST['description'] ?? ''),
            'source'          => $_POST['source'] ?? 'personal',
            'requested_by'    => trim($_POST['requested_by'] ?? ''),
            'priority'        => $_POST['priority'] ?? 'normal',
            'user_id'         => Auth::user()['id'],
        ];

        $errors = [];

        if ($data['title'] === '') {
            $errors['title'][] = 'Název je povinný';
        }

        if ($errors) {
            $this->view->errors = $errors;
            $this->view->data   = $data;
            return $this->render('work_orders/create');
        }

        $model = new WorkOrderModel();
        $id = $model->create($data);

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
}
