<?php

namespace App\Http\Requests\CreditCard;

use Illuminate\Foundation\Http\FormRequest;

class StoreCreditCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'digits' => is_string($this->digits) ? trim($this->digits) : $this->digits,
            'due_date' => $this->normalizeDay($this->due_date),
            'closing_date' => $this->normalizeDay($this->closing_date),
            'is_active' => $this->normalizeBoolean($this->is_active),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'digits' => ['required', 'string', 'size:4', 'regex:/^\d+$/'],
            'due_date' => ['required', 'string'],
            'closing_date' => ['required', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function normalizeBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    protected function normalizeDay(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
    }
}
