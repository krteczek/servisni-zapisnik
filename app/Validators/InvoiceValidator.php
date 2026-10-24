<?php

declare(strict_types=1);

namespace App\Validators;

use DateTimeImmutable;

final class InvoiceValidator
{
    public function __construct(private Validator $validator)
    {
    }

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
        $title = trim((string) ($post['title'] ?? ''));
        $issuedAt = trim((string) ($post['issued_at'] ?? ''));
        $dueDate = trim((string) ($post['due_date'] ?? ''));
        $note = trim((string) ($post['note'] ?? ''));

        if ($title === '') {
            $this->validator->addError(
                'title',
                'Název faktury je povinný.'
            );
        }

        if ($issuedAt === '') {
            $this->validator->addError(
                'issued_at',
                'Datum vystavení je povinné.'
            );
        } elseif (!$this->isValidDate($issuedAt)) {
            $this->validator->addError(
                'issued_at',
                'Datum vystavení není platné.'
            );
        }

        if ($dueDate === '') {
            $this->validator->addError(
                'due_date',
                'Datum splatnosti je povinné.'
            );
        } elseif (!$this->isValidDate($dueDate)) {
            $this->validator->addError(
                'due_date',
                'Datum splatnosti není platné.'
            );
        }

        $contactId = (int) ($post['contact_id'] ?? 0);

        if ($contactId < 0) {
            $this->validator->addError(
                'contact_id',
                'Neplatný zákazník.'
            );

            $contactId = 0;
        }

        $contactValidator = new ContactValidator($this->validator);
        $customer = $contactValidator->validate($post);

        $saveCustomer = isset($post['save_customer']);

        $tasks = $this->validateTasks($post['tasks'] ?? []);
        $lines = $this->validateLines($post['lines'] ?? []);

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
            'errors' => $this->validator->getErrors(),
        ];
    }

    /**
     * @param mixed $value
     * @return array<int, array<string, mixed>>
     */
    private function validateTasks(mixed $value): array
    {
        if (!is_array($value)) {
            $this->validator->addError(
                'tasks',
                'Úkoly faktury nejsou platné.'
            );

            return [];
        }

        $tasks = [];

        foreach ($value as $i => $task) {
            if (!is_array($task)) {
                $this->validator->addError(
                    "tasks.$i",
                    'Úkol faktury není platný.'
                );

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
                $this->validator->addError(
                    "tasks.$i.task_id",
                    'Úkol není platný.'
                );
            }

            if ($title === '') {
                $this->validator->addError(
                    "tasks.$i.title",
                    'Název úkolu je povinný.'
                );
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
     * @return array<int, array<string, mixed>>
     */
    private function validateLines(mixed $value): array
    {
        if (!is_array($value)) {
            $this->validator->addError(
                'lines',
                'Fakturační položky nejsou platné.'
            );

            return [];
        }

        $lines = [];

        foreach ($value as $i => $line) {
            if (!is_array($line)) {
                $this->validator->addError(
                    "lines.$i",
                    'Fakturační položka není platná.'
                );

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

            $unitPrice = (float) ($line['unit_price'] ?? 0);

            $hoursRaw = (int) ($line['hours'] ?? 0);
            $minutesRaw = (int) ($line['minutes'] ?? 0);

            if ($hoursRaw < 0 || $minutesRaw < 0) {
                $this->validator->addError(
                    "lines.$i",
                    'Čas nesmí být záporný.'
                );
            }

            $hours = max(0, $hoursRaw);
            $minutes = max(0, $minutesRaw);

            $quantity = (float) ($line['quantity'] ?? 0);

            if ($description === '') {
                $this->validator->addError(
                    "lines.$i.description",
                    'Popis položky je povinný.'
                );
            }

            if ($unit === '') {
                $this->validator->addError(
                    "lines.$i.unit",
                    'Jednotka množství je povinná.'
                );
            }

            if ($priceUnit === '') {
                $this->validator->addError(
                    "lines.$i.price_unit",
                    'Jednotka ceny je povinná.'
                );
            }

            if ($unitPrice < 0) {
                $this->validator->addError(
                    "lines.$i.unit_price",
                    'Cena nesmí být záporná.'
                );
            }

            if ($currency === '') {
                $this->validator->addError(
                    "lines.$i.currency",
                    'Měna je povinná.'
                );
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
