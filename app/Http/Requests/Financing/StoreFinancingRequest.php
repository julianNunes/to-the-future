<?php

namespace App\Http\Requests\Financing;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinancingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'description' => is_string($this->description) ? trim($this->description) : $this->description,
            'start_date' => $this->normalizeDate($this->start_date),
            'total' => $this->normalizeMoney($this->total),
            'fees_monthly' => $this->normalizeMoney($this->fees_monthly),
            'portion_total' => $this->normalizeInteger($this->portion_total),
            'remarks' => is_string($this->remarks) ? trim($this->remarks) : $this->remarks,
            'start_date_installment' => $this->normalizeDate($this->start_date_installment),
            'value_installment' => $this->normalizeMoney($this->value_installment),
        ]);
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'total' => ['required', 'numeric', 'gt:0'],
            'fees_monthly' => ['required', 'numeric', 'gt:0'],
            'portion_total' => ['required', 'integer', 'min:1'],
            'remarks' => ['nullable', 'string'],
            'start_date_installment' => ['required', 'date'],
            'value_installment' => ['required', 'numeric', 'gt:0'],
        ];
    }

    protected function normalizeDate(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
    }

    protected function normalizeInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    protected function normalizeMoney(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = preg_replace('/[^\d,.-]/', '', (string) $value);

        if ($normalized === null || $normalized === '') {
            return null;
        }

        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } elseif (str_contains($normalized, ',')) {
            $normalized = str_replace(',', '.', $normalized);
        }

        return is_numeric($normalized) ? (float) $normalized : null;
    }
}
