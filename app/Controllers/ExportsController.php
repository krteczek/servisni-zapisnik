<?php
declare(strict_types=1);

namespace App\Controllers;
use App\Models\TaskModel;
use App\Models\TeamModel;
use App\Core\Url;
use App\Core\Csrf;
use App\Core\Controller;
use App\Core\ViewContext;



final class ExportsController extends Controller
{

private function dataToView(): void
    {
        $taskModel = new TaskModel();
        $teamModel = new TeamModel();

        $date   = $_GET['date'] ?? date('Y-m-d');
        $teamId = $_GET['team_id'] ?? null;
        $status = $_GET['status'] ?? null;

        $tasks = $taskModel->filter([
            'date'    => $date,
            'team_id' => $teamId,
            'status'  => $status,
        ]);

        $this->view->tasks  = $tasks;
        $this->view->teams  = $teamModel->all();
        $this->view->data['date']   = $date;
        $this->view->data['teamId'] = $teamId;
        $this->view->data['status'] = $status;
        //return 
    }
    public function exportTasksGet(): string
    {
        $this->dataToView();

        return $this->render('exports/data/taskExports');
    }

    public function exportTasksPost(): string
    {

        $rawIds = $_POST['ids'] ?? [];
        if (!is_array($rawIds)) {
            $rawIds = [];
        }
        $ids = array_values(array_unique(
            array_filter(
                array_map('intval', $rawIds),
                static fn (int $id): bool => $id > 0
            )
        ));


        if ($ids === []) {
            $this->dataToView();
            $this->view->data['date'] = $_GET['date'] ?? null;
            $this->addError('ids', 'Nevybrána žádná data pro export.');

            // 🔁 znovunačtení dat stejně jako GET
            return $this->render('exports/data/taskExports');
        }

        $taskModel = new TaskModel();
        $tasks     = $taskModel->findByIds($ids);

        if ($tasks === []) {
            $this->addError('ids', 'Žádná data nebyla nalezena.');
            return $this->exportTasksGet();
        }

        $csv = $this->toCsv($tasks);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="tasks_export.csv"');

        echo $csv;
        exit;
    }

    /**
     * Jednoduchý CSV export
    * @param array<int, array<string, mixed>> $rows
    * @return string
    */
    private function toCsv(array $rows): string
    {
        if ($rows === []) {
            return '';
        }

        $out = fopen('php://temp', 'r+');

        // header
        fputcsv($out, array_keys($rows[0]));

        // data
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }

        //rewind($out);
        //return stream_get_contents($out);

        rewind($out);
        $content = stream_get_contents($out);
        fclose($out);

        return $content !== false ? $content : '';
    }
}