<?php

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;

class CloneBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'year' => $this->normalizeYear($this->year),
            'month' => $this->normalizeMonth($this->month),
            'includeProvisions' => $this->normalizeBoolean($this->includeProvisions),
            'cloneBugdetExpenses' => $this->normalizeBoolean($this->cloneBugdetExpenses),
            'cloneBugdetIncomes' => $this->normalizeBoolean($this->cloneBugdetIncomes),
            'cloneBugdetGoals' => $this->normalizeBoolean($this->cloneBugdetGoals),
        ]);
    }

    public function rules(): array
    {
        return [
            'year' => ['required', 'string', 'size:4', 'regex:/^\d{4}$/'],
            'month' => ['required', 'string', 'size:2', 'regex:/^\d{2}$/'],
            'includeProvisions' => ['nullable', 'boolean'],
            'cloneBugdetExpenses' => ['nullable', 'boolean'],
            'cloneBugdetIncomes' => ['nullable', 'boolean'],
            'cloneBugdetGoals' => ['nullable', 'boolean'],
        ];
    }

    protected function normalizeBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    protected function normalizeMonth(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return str_pad((string) (int) $value, 2, '0', STR_PAD_LEFT);
    }

    protected function normalizeYear(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
    }
}
