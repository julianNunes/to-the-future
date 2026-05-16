<?php

namespace App\Http\Requests\BudgetExpense;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetExpenseRequest extends FormRequest
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
            'portion' => $this->normalizeInteger($this->portion),
            'portion_total' => $this->normalizeInteger($this->portion_total),
            'group' => is_string($this->group) ? trim($this->group) : $this->group,
            'remarks' => is_string($this->remarks) ? trim($this->remarks) : $this->remarks,
            'paid' => $this->normalizeBoolean($this->paid),
            'share_value' => $this->normalizeMoney($this->share_value, true),
            'share_user_id' => $this->normalizeInteger($this->share_user_id),
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
            'portion' => ['nullable', 'integer', 'min:1', 'required_with:portion_total'],
            'portion_total' => ['nullable', 'integer', 'min:2', 'required_with:portion'],
            'group' => ['required', 'string'],
            'remarks' => ['nullable', 'string'],
            'paid' => ['required', 'boolean'],
            'share_value' => ['nullable', 'numeric', 'gt:0', 'required_with:share_user_id'],
            'share_user_id' => ['nullable', 'integer', 'required_with:share_value'],
            'tags' => ['nullable', 'array'],
            'tags.*.name' => ['required', 'string'],
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

    protected function normalizeMoney(mixed $value, bool $nullWhenNonPositive = false): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $number = (float) $value;

            if ($nullWhenNonPositive && $number <= 0) {
                return null;
            }

            return $number;
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

        if (! is_numeric($normalized)) {
            return null;
        }

        $number = (float) $normalized;

        if ($nullWhenNonPositive && $number <= 0) {
            return null;
        }

        return $number;
    }
}
