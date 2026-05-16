<?php

namespace App\Http\Requests\PrepaidCardExtract;

use Illuminate\Foundation\Http\FormRequest;

class StorePrepaidCardExtractRequest extends FormRequest
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
            'credit' => $this->normalizeMoney($this->credit),
            'credit_date' => $this->normalizeDate($this->credit_date),
            'remarks' => $this->normalizeNullableString($this->remarks),
            'prepaid_card_id' => $this->normalizeInteger($this->prepaid_card_id),
        ]);
    }

    public function rules(): array
    {
        return [
            'year' => ['required', 'string', 'size:4', 'regex:/^\d{4}$/'],
            'month' => ['required', 'string', 'regex:/^(0[1-9]|1[0-2])$/'],
            'credit' => ['required', 'numeric', 'gt:0'],
            'credit_date' => ['required', 'date_format:Y-m-d'],
            'remarks' => ['nullable', 'string'],
            'prepaid_card_id' => ['required', 'integer'],
        ];
    }

    protected function normalizeInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
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

    protected function normalizeNullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return $value;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }
}
