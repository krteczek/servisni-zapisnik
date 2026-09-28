<?php
declare(strict_types=1);

namespace App\Validators;

class Validator
{
    /**
     * @var array<string, list<string>>
     */
    protected array $errors = [];

    public function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    /**
     * @return list<string>|string
     */
    public function getError(string $key): array|string
    {
        return $this->errors[$key] ?? '';
    }

    /**
     * @return array<string, list<string>>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }

    public function required(
        string $field,
        string $value,
        string $message
    ): void {
        if ($value === '') {
            $this->addError($field, $message);
        }
    }

    public function maxLength(
        string $field,
        ?string $value,
        int $max,
        string $label
    ): void {
        if ($value === null) {
            return;
        }

        if (mb_strlen($value, 'UTF-8') > $max) {
            $this->addError(
                $field,
                "{$label} může mít maximálně {$max} znaků"
            );
        }
    }
}