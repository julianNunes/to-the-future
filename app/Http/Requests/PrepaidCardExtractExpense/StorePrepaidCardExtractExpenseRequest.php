<?php

namespace App\Http\Requests\PrepaidCardExtractExpense;

use Illuminate\Foundation\Http\FormRequest;

class StorePrepaidCardExtractExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'prepaid_card_id' => $this->normalizeInteger($this->prepaid_card_id),
            'extract_id' => $this->normalizeInteger($this->extract_id),
            'description' => $this->normalizeNullableString($this->description),
            'date' => $this->normalizeDate($this->date),
            'value' => $this->normalizeMoney($this->value),
            'group' => $this->normalizeNullableString($this->group),
            'remarks' => $this->normalizeNullableString($this->remarks),
            'share_value' => $this->normalizeMoney($this->share_value, true),
            'share_user_id' => $this->normalizeInteger($this->share_user_id),
            'tags' => is_array($this->tags) ? $this->tags : [],
        ]);
    }

    public function rules(): array
    {
        return [
            'prepaid_card_id' => ['required', 'integer'],
            'extract_id' => ['required', 'integer'],
            'description' => ['required', 'string'],
            'date' => ['required', 'date_format:Y-m-d'],
            'value' => ['required', 'numeric', 'gt:0'],
            'group' => ['required', 'string', 'in:WEEK_1,WEEK_2,WEEK_3,WEEK_4'],
            'remarks' => ['nullable', 'string'],
            'share_value' => ['nullable', 'numeric', 'gt:0', 'required_with:share_user_id'],
            'share_user_id' => ['nullable', 'integer', 'required_with:share_value'],
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
}
