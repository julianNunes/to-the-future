<?php

namespace App\Http\Requests\PrepaidCardExtractExpense;

use Illuminate\Foundation\Http\FormRequest;

class StoreImportPrepaidCardExtractExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'extract_id' => $this->normalizeInteger($this->extract_id),
            'data' => $this->normalizeRows($this->data),
        ]);
    }

    public function rules(): array
    {
        return [
            'extract_id' => ['required', 'integer'],
            'data' => ['required', 'array', 'min:1'],
            'data.*.description' => ['required', 'string'],
            'data.*.date' => ['required', 'date_format:Y-m-d'],
            'data.*.value' => ['required', 'numeric', 'gt:0'],
            'data.*.group' => ['required', 'string', 'in:WEEK_1,WEEK_2,WEEK_3,WEEK_4'],
            'data.*.remarks' => ['nullable', 'string'],
            'data.*.share_value' => ['nullable', 'numeric', 'gt:0', 'required_with:data.*.share_user_id'],
            'data.*.share_user_id' => ['nullable', 'integer', 'required_with:data.*.share_value'],
            'data.*.tags' => ['nullable', 'array'],
            'data.*.tags.*.name' => ['required', 'string'],
        ];
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

    protected function normalizeNullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return $value;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
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

    protected function normalizeRows(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_map(function ($row) {
            if (! is_array($row)) {
                return [];
            }

            return [
                'description' => $this->normalizeNullableString($row['description'] ?? null),
                'date' => $this->normalizeDate($row['date'] ?? null),
                'value' => $this->normalizeMoney($row['value'] ?? null),
                'group' => $this->normalizeNullableString($row['group'] ?? null),
                'remarks' => $this->normalizeNullableString($row['remarks'] ?? null),
                'share_value' => $this->normalizeMoney($row['share_value'] ?? null, true),
                'share_user_id' => $this->normalizeInteger($row['share_user_id'] ?? null),
                'tags' => is_array($row['tags'] ?? null) ? $row['tags'] : [],
            ];
        }, $value);
    }
}
