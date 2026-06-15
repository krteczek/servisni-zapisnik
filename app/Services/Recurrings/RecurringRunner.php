<?php
declare(strict_types=1);

namespace App\Services\Recurrings;

use App\Core\Database;
use App\Core\TenantContext;
use App\Core\Config;
use App\Core\LoggerHolder;
use App\Models\TaskModel;
use App\Models\RecurringRunnerModel;
use PDO;
use Throwable;

final class RecurringRunner
{
    private const JOB_KEY = 'recurring_runner';

    public static function run(): void
    {
        $pdo = Database::admin();

        $lockSeconds = (int) Config::get('recurring.runner.lock_seconds', 30);

        // =========================
        // 1) ATOMICKÝ LOCK
        // =========================
        $stmt = $pdo->prepare("
            UPDATE system_jobs
            SET locked_until = DATE_ADD(NOW(), INTERVAL :sec SECOND)
            WHERE job_key = :key
              AND (locked_until IS NULL OR locked_until < NOW())
        ");

        $stmt->execute([
            'sec' => $lockSeconds,
            'key' => self::JOB_KEY,
        ]);

        if ($stmt->rowCount() === 0) {
            return;
        }

        try {
            self::processBatch();
        } catch (Throwable $e) {
            LoggerHolder::get()->error('RecurringRunner: run failed', [
                'message' => $e->getMessage(),
            ]);
        }

        // =========================
        // 2) UNLOCK
        // =========================
        $pdo->prepare("
            UPDATE system_jobs
            SET locked_until = NULL,
                last_run_at = NOW()
            WHERE job_key = :key
        ")->execute(['key' => self::JOB_KEY]);
    }

    private static function processBatch(): void
    {
        $pdo = Database::admin();

        $batchSize = (int) Config::get('recurring.runner.batch_size_companies', 20);

        $job = $pdo->prepare("
            SELECT last_company_id
            FROM system_jobs
            WHERE job_key = :key
            LIMIT 1
        ");
        $job->execute(['key' => self::JOB_KEY]);

        $lastCompanyId = (int) ($job->fetchColumn() ?: 0);

        $companies = $pdo->prepare("
            SELECT *
            FROM companies
            WHERE id > :last
              AND active = 1
            ORDER BY id ASC
            LIMIT {$batchSize}
        ");

        $companies->execute(['last' => $lastCompanyId]);
        $rows = $companies->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) {
            // restart
            $pdo->prepare("
                UPDATE system_jobs
                SET last_company_id = 0
                WHERE job_key = :key
            ")->execute(['key' => self::JOB_KEY]);

            return;
        }

        foreach ($rows as $company) {
            self::processCompany($company);
            $lastCompanyId = (int) $company['id'];
        }

        $pdo->prepare("
            UPDATE system_jobs
            SET last_company_id = :id
            WHERE job_key = :key
        ")->execute([
            'id'  => $lastCompanyId,
            'key' => self::JOB_KEY,
        ]);
    }

private static function processCompany(array $company): void
{
    Database::useWorkDatabase($company['db_name']);
    TenantContext::set((int)$company['id']);

    $pdo = Database::work();
    $taskLimit = (int) Config::get('recurring.runner.batch_size_tasks', 20);

    $recurringModel = new RecurringRunnerModel();
    $taskModel = new TaskModel();

    try {
        $recurrings = $recurringModel->findDueTasks((int)$company['id'], $taskLimit);

        if (!$recurrings) {
            return;
        }

        foreach ($recurrings as $rt) {
LoggerHolder::get()->info('RecurringRunner: processing recurring', [
    'rt_id' => $rt['id'],
    'task_id' => $rt['task_id'],
    'next_due_date' => $rt['next_due_date'],
]);
            // =========================
            // 1) LOCK
            // =========================
            if (!$recurringModel->lockTask(
                (int)$rt['id'],
                (int)$company['id']
            )) {
LoggerHolder::get()->info('RecurringRunner: processing continue', [
    'rt_id' => $rt['id'],
    'task_id' => $rt['task_id'],
    'next_due_date' => $rt['next_due_date'],
]);
                continue;
            }

            

            // =========================
            // 3) SOURCE TASK
            // =========================
            $source = $taskModel->find((int)$rt['task_id']);
LoggerHolder::get()->info('RecurringRunner: source loaded', [
    'rt_id' => $rt['id'],
    'source_found' => $source ? true : false,
]);
            if (!$source) {
                LoggerHolder::get()->warning('RecurringRunner: missing source task', [
                    'rt_id'   => $rt['id'],
                    'task_id' => $rt['task_id'],
                ]);
                $recurringModel->clearProcessing((int)$rt['id'], (int)$company['id']);
                continue;
            }

            // =========================
            // 4) TRANSACTION
            // =========================
            $pdo->beginTransaction();

            try {
                $date = date('Y-m-d');
                $iterations = 0;

                while ($rt['next_due_date'] <= $date){

                    $iterations++;
                    if ($iterations > 100) {
                        throw new \RuntimeException(sprintf(
                            'Recurring task %d exceeded 100 iterations. next_due_date=%s, today=%s, frequency=%s/%d',
                            $rt['id'],
                            $rt['next_due_date'],
                            $date,
                            $rt['frequency_type'],
                            $rt['frequency_value']
                        ));
                    }

                    if (!$recurringModel->taskAlreadyExistsForDate(
                        (int)$rt['id'],
                        (int)$company['id'],
                        $rt['next_due_date']
                    )) {

                        $taskModel->create([
                            'team_id'            => $source['team_id'],
                            'work_order_id'      => $source['work_order_id'],
                            'recurring_task_id'  => $rt['id'],
                            'title'              => $source['title'],
                            'description'        => $source['description'],
                            'status'             => 'open',
                            'created_by_user_id' => $source['created_by_user_id'],
                            'due_date'           => $rt['next_due_date'],
                        ]);
                    }

                    $rt['next_due_date'] = self::calculateNextDate(
                        $rt['next_due_date'],
                        $rt['frequency_type'],
                        (int)$rt['frequency_value']
                    );
                }

                $recurringModel->updateNextDueDate(
                    (int)$rt['id'],
                    (int)$company['id'],
                    $rt['next_due_date']
                );

                $pdo->commit();

                $recurringModel->clearProcessing(
                    (int)$rt['id'],
                    (int)$company['id']
                );

            } catch (Throwable $e) {

                $pdo->rollBack();

                $recurringModel->clearProcessing(
                    (int)$rt['id'],
                    (int)$company['id']
                );

                LoggerHolder::get()->error(
                    'RecurringRunner: transaction failed',
                    [
                        'message' => $e->getMessage(),
                        'rt_id'   => $rt['id'],             
                        'file'    => $e->getFile(),
                        'line'    => $e->getLine(),
                        'trace'   => $e->getTraceAsString(),
                   ]
                );
            }
        }

    } catch (Throwable $e) {
        LoggerHolder::get()->error('RecurringRunner: processCompany failed', [
            'message'    => $e->getMessage(),
            'company_id' => $company['id'],
            
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),

       ]);
    } finally {
        TenantContext::clear();
    }
}
    private static function calculateNextDate(
        string $current,
        string $type,
        int $value
    ): string {
        $date = new \DateTime($current);

        switch ($type) {
            case 'daily':
                $date->modify("+{$value} day");
                break;

            case 'weekly':
                $date->modify("+{$value} week");
                break;

            case 'monthly':
                $date->modify("+{$value} month");
                break;

            case 'yearly':
                $date->modify("+{$value} year");
                break;

            default:
                throw new \InvalidArgumentException("Unknown frequency type: {$type}");
        }

        return $date->format('Y-m-d');
    }
}