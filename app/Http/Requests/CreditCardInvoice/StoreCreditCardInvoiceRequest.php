<?php

namespace App\Http\Requests\CreditCardInvoice;

use Illuminate\Foundation\Http\FormRequest;

class StoreCreditCardInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'due_date' => $this->normalizeDate($this->due_date),
            'closing_date' => $this->normalizeDate($this->closing_date),
            'year' => $this->normalizeYear($this->year),
            'month' => $this->normalizeMonth($this->month),
            'credit_card_id' => $this->normalizeInteger($this->credit_card_id),
            'automatic_generate' => $this->normalizeBoolean($this->automatic_generate),
        ]);
    }

    public function rules(): array
    {
        return [
            'due_date' => ['required', 'date_format:Y-m-d'],
            'closing_date' => ['required', 'date_format:Y-m-d'],
            'year' => ['required', 'string', 'size:4', 'regex:/^\d{4}$/'],
            'month' => ['required', 'string', 'regex:/^(0[1-9]|1[0-2])$/'],
            'credit_card_id' => ['required', 'integer'],
            'automatic_generate' => ['nullable', 'boolean'],
        ];
    }

    protected function normalizeBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    protected function normalizeInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    protected function normalizeDate(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $normalized = trim($value);

        if ($normalized === '') {
            return null;
        }

        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $normalized, $matches)) {
            return sprintf('%04d-%02d-%02d', (int) $matches[1], (int) $matches[2], (int) $matches[3]);
        }

        return $normalized;
    }

    protected function normalizeYear(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return str_pad(trim((string) $value), 4, '0', STR_PAD_LEFT);
    }

    protected function normalizeMonth(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return str_pad(trim((string) $value), 2, '0', STR_PAD_LEFT);
    }
}
