<?php

declare(strict_types=1);

namespace App\Validators;

use DateTimeImmutable;

final class InvoiceValidator
{
    /**
     * Zvaliduje vstupní data faktury.
     *
     * Validator pouze ověřuje vstupní hodnoty.
     * Výpočty a převod do interní struktury provádí InvoiceNormalizer.
     *
     * @param array<string, mixed> $post
     * @return array{
     *     title: string,
     *     issued_at: string,
     *     due_date: string,
     *     note: string,
     *     contact_id: int,
     *     save_customer: bool,
     *     customer: array<string, string>,
     *     tasks: array<int, array<string, mixed>>,
     *     lines: array<int, array<string, mixed>>,
     *     errors: array<string, list<string>>
     * }
     */
    public function validate(array $post): array
    {
        /** @var array<string, list<string>> $errors */
        $errors = [];

        $title = trim((string) ($post['title'] ?? ''));
        $issuedAt = trim((string) ($post['issued_at'] ?? ''));
        $dueDate = trim((string) ($post['due_date'] ?? ''));
        $note = trim((string) ($post['note'] ?? ''));

        if ($title === '') {
            $errors['title'][] = 'Název faktury je povinný.';
        }

        if ($issuedAt === '') {
            $errors['issued_at'][] =
                'Datum vystavení je povinné.';
        } elseif (!$this->isValidDate($issuedAt)) {
            $errors['issued_at'][] =
                'Datum vystavení není platné.';
        }

        if ($dueDate === '') {
            $errors['due_date'][] =
                'Datum splatnosti je povinné.';
        } elseif (!$this->isValidDate($dueDate)) {
            $errors['due_date'][] =
                'Datum splatnosti není platné.';
        }

        $contactId = (int) ($post['contact_id'] ?? 0);

        if ($contactId < 0) {
            $errors['contact_id'][] =
                'Neplatný zákazník.';
            $contactId = 0;
        }

        $contactValidator = new ContactValidator();

        $customer = $contactValidator->validate($post);

        $errors = array_merge(
            $errors,
            $contactValidator->getErrors()
        );

        $saveCustomer = isset($post['save_customer']);

        $tasks = $this->validateTasks(
            $post['tasks'] ?? [],
            $errors
        );

        $lines = $this->validateLines(
            $post['lines'] ?? [],
            $errors
        );

        return [
            'title' => $title,
            'issued_at' => $issuedAt,
            'due_date' => $dueDate,
            'note' => $note,
            'contact_id' => $contactId,
            'save_customer' => $saveCustomer,
            'customer' => $customer,
            'tasks' => $tasks,
            'lines' => $lines,
            'errors' => $errors,
        ];
    }

    /**
     * @param mixed $value
     * @param array<string, list<string>> $errors
     * @return array<int, array<string, mixed>>
     */
    private function validateTasks(
        mixed $value,
        array &$errors
    ): array {
        if (!is_array($value)) {
            $errors['tasks'][] =
                'Úkoly faktury nejsou platné.';

            return [];
        }

        $tasks = [];

        foreach ($value as $i => $task) {
            if (!is_array($task)) {
                $errors["tasks.$i"][] =
                    'Úkol faktury není platný.';
                continue;
            }

            $taskId = (int) ($task['task_id'] ?? 0);
            $title = trim((string) ($task['title'] ?? ''));

            $minutes = max(
                0,
                (int) ($task['minutes'] ?? 0)
            );

            $kilometers = max(
                0,
                (float) ($task['kilometers'] ?? 0)
            );

            if ($taskId <= 0) {
                $errors["tasks.$i.task_id"][] =
                    'Úkol není platný.';
            }

            if ($title === '') {
                $errors["tasks.$i.title"][] =
                    'Název úkolu je povinný.';
            }

            $tasks[] = [
                'task_id' => $taskId,
                'title' => $title,
                'minutes' => $minutes,
                'kilometers' => $kilometers,
            ];
        }

        return $tasks;
    }

    /**
     * @param mixed $value
     * @param array<string, list<string>> $errors
     * @return array<int, array<string, mixed>>
     */
    private function validateLines(
        mixed $value,
        array &$errors
    ): array {
        if (!is_array($value)) {
            $errors['lines'][] =
                'Fakturační položky nejsou platné.';

            return [];
        }

        $lines = [];

        foreach ($value as $i => $line) {
            if (!is_array($line)) {
                $errors["lines.$i"][] =
                    'Fakturační položka není platná.';
                continue;
            }

            $description = trim(
                (string) ($line['description'] ?? '')
            );

            $unit = trim(
                (string) ($line['unit'] ?? '')
            );

            $priceUnit = trim(
                (string) ($line['price_unit'] ?? '')
            );

            $currency = strtoupper(
                trim((string) ($line['currency'] ?? 'CZK'))
            );

            $unitPrice = (float) (
                $line['unit_price'] ?? 0
            );

            $hours = max(
                0,
                (int) ($line['hours'] ?? 0)
            );

            $minutes = max(
                0,
                (int) ($line['minutes'] ?? 0)
            );

            $quantity = (float) (
                $line['quantity'] ?? 0
            );

            if ($description === '') {
                $errors["lines.$i.description"][] =
                    'Popis položky je povinný.';
            }

            if ($unit === '') {
                $errors["lines.$i.unit"][] =
                    'Jednotka množství je povinná.';
            }

            if ($priceUnit === '') {
                $errors["lines.$i.price_unit"][] =
                    'Jednotka ceny je povinná.';
            }

            if ($unitPrice < 0) {
                $errors["lines.$i.unit_price"][] =
                    'Cena nesmí být záporná.';
            }

            if ($currency === '') {
                $errors["lines.$i.currency"][] =
                    'Měna je povinná.';
            }

            if ($hours < 0 || $minutes < 0) {
                $errors["lines.$i"][] =
                    'Čas nesmí být záporný.';
            }

            $lines[] = [
                'description' => $description,
                'quantity' => $quantity,
                'unit' => $unit,
                'price_unit' => $priceUnit,
                'unit_price' => $unitPrice,
                'currency' => $currency,
                'hours' => $hours,
                'minutes' => $minutes,
            ];
        }

        return $lines;
    }

    private function isValidDate(string $date): bool
    {
        $value = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $date
        );

        return $value !== false
            && $value->format('Y-m-d') === $date;
    }
}