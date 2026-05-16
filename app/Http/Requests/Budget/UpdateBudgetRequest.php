<?php

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'start_week_1' => $this->normalizeDate($this->start_week_1),
            'end_week_1' => $this->normalizeDate($this->end_week_1),
            'start_week_2' => $this->normalizeDate($this->start_week_2),
            'end_week_2' => $this->normalizeDate($this->end_week_2),
            'start_week_3' => $this->normalizeDate($this->start_week_3),
            'end_week_3' => $this->normalizeDate($this->end_week_3),
            'start_week_4' => $this->normalizeDate($this->start_week_4),
            'end_week_4' => $this->normalizeDate($this->end_week_4),
            'closed' => $this->normalizeBoolean($this->closed),
        ]);
    }

    public function rules(): array
    {
        return [
            'start_week_1' => ['nullable', 'date_format:Y-m-d', 'required_with:end_week_1'],
            'end_week_1' => ['nullable', 'date_format:Y-m-d', 'required_with:start_week_1', 'after_or_equal:start_week_1'],
            'start_week_2' => ['nullable', 'date_format:Y-m-d', 'required_with:end_week_2'],
            'end_week_2' => ['nullable', 'date_format:Y-m-d', 'required_with:start_week_2', 'after_or_equal:start_week_2'],
            'start_week_3' => ['nullable', 'date_format:Y-m-d', 'required_with:end_week_3'],
            'end_week_3' => ['nullable', 'date_format:Y-m-d', 'required_with:start_week_3', 'after_or_equal:start_week_3'],
            'start_week_4' => ['nullable', 'date_format:Y-m-d', 'required_with:end_week_4'],
            'end_week_4' => ['nullable', 'date_format:Y-m-d', 'required_with:start_week_4', 'after_or_equal:start_week_4'],
            'closed' => ['nullable', 'boolean'],
        ];
    }

    protected function normalizeBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    protected function normalizeDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
    }
}
