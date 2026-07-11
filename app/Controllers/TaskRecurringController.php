<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Config;
use App\Core\Flash;
use App\Core\LoggerHolder;
use App\Core\Url;
use App\Core\Controller;
use App\Core\ViewContext;
use App\Models\TaskModel;
use App\Models\RecurringTaskModel;
use App\Models\TeamModel;
use App\Services\Tasks\TaskType;
use App\Services\Tasks\TaskStatus;

use Throwable;

use Override;

final class TaskRecurringController extends Controller
{
    private RecurringTaskModel $model;

    public function __construct(ViewContext $view)
    {        
        parent::__construct($view);
        $this->model = new RecurringTaskModel();
    }

    private function getTaskRecurringOrRedirect(int $taskId): array
	{
        // možná ušetříme dotaz
        if ($taskId < 1) {
            Flash::error('Úkol neexistuje');
            Url::redirect('/{tenant}/tasks/#main');	        
        }

		$task = (new TaskModel())->find((int) $taskId);
		if(!$task) {
            // takový úkol prostě neexistuje
            Flash::error('Úkol neexistuje');
            Url::redirect('/{tenant}/tasks/#main');			
		}
        if (TaskType::isNormal($task['task_type'])) {
            //tohle je normální úkol, ne šablona pro opakování
            Flash::error('Tento úkol není šablonou pro opakované úkoly.');
            Url::redirect('/{tenant}/tasks/' . $taskId . '/edit/#main');
        }        
        if (TaskType::isInstance($task['task_type'])) {
            // úkol existuje ale není šablonou pro opakování, je to jen instance
            Flash::error('Tento úkol není šablonou pro opakované úkoly.');
            Url::redirect('/{tenant}/tasks/' . $taskId . '/edit/#main');;
        }
        $task['count_instances'] = (new TaskModel())->countRecurringInstances($task['recurring_task_id']); 
		return $task;	
	}

    public function recurringGet(int $taskId): string
    {
        $task = $this->getTaskRecurringOrRedirect($taskId);
        //var_dump($task);
        $this->ensureRecurringEditable($task);
        $recurring = [];
        try {
            
            $recurring = ($this->model->find($task['recurring_task_id']) ?? []);
            //var_dump($recurring);
 
            if (!$recurring) {
                Flash::error(
                    'Opakovací šablona nebyla nalezena.'
                );
                Url::redirect('/{tenant}/tasks/' . $taskId . '/edit/#main');
            }
        } catch (Throwable $e) {
            LoggerHolder::get()->error('TaskRecurringController.recurringGet FAILED', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            // 👤 USER MESSAGE
            Flash::error('Nepodařilo se načíst úkol, omlouváme se. Zkuste to prosím znovu později.');
            Url::redirect('/{tenant}/tasks/' . $taskId . '/edit/#main');
        }

        $this->view->task = $task;
        $this->view->data = $recurring;

        return $this->render('tasks/recurring');
    }


    public function recurringPost(int $taskId): string
    {
        $this->checkCsrf();

        $task = $this->getTaskRecurringOrRedirect($taskId);
        $this->ensureRecurringEditable($task);
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
            $row = $this->model->update($task['recurring_task_id'],$toDb);
            
            Flash::success('Opakování bylo uloženo');

            //Url::redirect('/{tenant}/tasks/' . $task['id'] . '/edit/#main');
            Url::redirect('/{tenant}/tasks/' . $task['id'] . '/recurringDetail/#main');

        }
        catch (Throwable $e)
        {
            LoggerHolder::get()->error('TaskRecurringController.recurringPost: FAILED', [
                        'message'   => $e->getMessage(),
                        'file'      => $e->getFile(),
                        'line'      => $e->getLine(),
                        'trace'     => $e->getTraceAsString(),
                        
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
            array_key_exists(($data['frequency_type'] ?? ''), $defaults['frequencies']) 
            //array_key_exists($data['frequency_type'], $defaults['frequencies'])
                ? $data['frequency_type']
                : $defaults['default']['frequency_type'],

            'frequency_value' => self::isBetween(
                (int) $defaults['limits']['min_frequency_value'],
                (int) $defaults['limits']['max_frequency_value'],
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

    private static function isBetween(int $num1, int $num2, int $num3): bool
    {
            $numbers = [$num1, $num2];

            if ($num3 >= min($numbers) && $num3 <= max($numbers)) {
                return true;
            } else {
                return false;
            }
    }


    public function recurringDetail(int $taskId): string
    {

        $task = $this->getTaskRecurringOrRedirect($taskId);
        //var_dump($task);
        $recurring = [];
        try {
            
            $recurring = ($this->model->find($task['recurring_task_id']) ?? []);
            //var_dump($recurring);
 
            if (!$recurring) {
                Flash::error(
                    'Opakovací šablona nebyla nalezena.'
                );
                Url::redirect('/{tenant}/tasks/' . $taskId . '/edit/#main');
            }
        } catch (Throwable $e) {
            LoggerHolder::get()->error('TaskRecurringController.recurringGet FAILED', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            // 👤 USER MESSAGE
            Flash::error('Nepodařilo se načíst úkol, omlouváme se. Zkuste to prosím znovu později.');
            Url::redirect('/{tenant}/tasks/' . $taskId . '/edit/#main');
        }

        $this->view->task = $task;
        $this->view->data = $recurring;
        $this->view->team = (new TeamModel())->find($task['team_id']);
        $this->view->order = (new \App\Models\WorkOrderModel())->find($task['work_order_id']);

        return $this->render('tasks/recurringDetail');
    }


    public function ensureRecurringTaskClosable(array $task): void
    {
        if (empty($task['recurring_task_id']) || TaskType::isNormal($task['task_type'])) {
            Flash::info('Tato funkce je určena pouze pro opakující se úkoly.');
            Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main');
        }

        if ($task['status'] === 'done') {
            Flash::info("Tento úkol nelze uzavřít, protože je již uzavřený.");
            Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main');
        }

        if (TaskStatus::isCancelled($task['status'])) {
            Flash::error("Tento úkol nelze uzavřít, protože je již zrušený.");
            Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main');
        }
    }


    public function doneRecurring(int $taskId): void
    {
        $task = $this->getTaskRecurringOrRedirect($taskId);
        $this->ensureRecurringTaskClosable($task);
//dc($task);
        try {
            $ok = (new TaskModel())->closeRecurringTask($taskId, 'done');

            Flash::success('Úkol byl uzavřen.');
            Url::redirect('/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#taskId_' . $taskId);
            
        } catch (Throwable $e) {

                // 🔥 TECHNICKÝ LOG
                LoggerHolder::get()->error('TaskController.doneRecurring FAILED', [
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


    /** 
     * Ověří, zda je opakující se úkol upravitelný.
     *
     * @param array $task Úkol k ověření
     * @return void
     */
    private function ensureRecurringEditable(array $task): void
    {
        if ($task['status'] === 'done') {
            Flash::error(
                'Tuto šablonu již nelze upravovat, protože byla dokončena.'
            );

            Url::redirect(
                '/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main'
            );
        }

        if (TaskStatus::isCancelled($task['status'])) {
            Flash::error(
                'Tuto šablonu již nelze upravovat, protože byla zrušena.'
            );

            Url::redirect(
                '/{tenant}/work-orders/' . $task['work_order_id'] . '/detail/#main'
            );
        }
    }
        
 }