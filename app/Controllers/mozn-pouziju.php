<?php
declare(strict_types=1);

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

