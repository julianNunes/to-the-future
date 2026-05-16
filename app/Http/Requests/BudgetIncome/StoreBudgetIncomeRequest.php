<?php

namespace App\Http\Requests\BudgetIncome;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetIncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'budget_id' => $this->normalizeInteger($this->budget_id),
            'description' => is_string($this->description) ? trim($this->description) : $this->description,
            'date' => is_string($this->date) ? trim($this->date) : $this->date,
            'value' => $this->normalizeMoney($this->value),
            'remarks' => is_string($this->remarks) ? trim($this->remarks) : $this->remarks,
            'tags' => is_array($this->tags) ? $this->tags : [],
        ]);
    }

    public function rules(): array
    {
        return [
            'budget_id' => ['required', 'integer'],
            'description' => ['required', 'string'],
            'date' => ['required', 'date_format:Y-m-d'],
            'value' => ['required', 'numeric', 'gt:0'],
            'remarks' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*.name' => ['required', 'string'],
        ];
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
