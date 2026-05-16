<?php

namespace App\Http\Requests\CreditCardInvoiceExpense;

use Illuminate\Foundation\Http\FormRequest;

class StoreImportCreditCardInvoiceExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'invoice_id' => $this->normalizeInteger($this->invoice_id),
            'data' => $this->normalizeDataRows($this->data),
        ]);
    }

    public function rules(): array
    {
        return [
            'invoice_id' => ['required', 'integer'],
            'data' => ['required', 'array', 'min:1'],
            'data.*.description' => ['required', 'string'],
            'data.*.date' => ['required', 'date_format:Y-m-d'],
            'data.*.value' => ['required', 'numeric', 'gt:0'],
            'data.*.group' => ['required', 'string', 'in:PORTION,WEEK_1,WEEK_2,WEEK_3,WEEK_4'],
            'data.*.portion' => ['nullable', 'integer', 'min:1', 'required_with:data.*.portion_total'],
            'data.*.portion_total' => ['nullable', 'integer', 'min:2', 'required_with:data.*.portion'],
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

    protected function normalizeDataRows(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_map(function ($row) {
            if (! is_array($row)) {
                return [];
            }

            return [
                'description' => is_string($row['description'] ?? null) ? trim($row['description']) : ($row['description'] ?? null),
                'date' => is_string($row['date'] ?? null) ? trim($row['date']) : ($row['date'] ?? null),
                'value' => $this->normalizeMoney($row['value'] ?? null),
                'group' => is_string($row['group'] ?? null) ? trim($row['group']) : ($row['group'] ?? null),
                'portion' => $this->normalizeInteger($row['portion'] ?? null),
                'portion_total' => $this->normalizeInteger($row['portion_total'] ?? null),
                'remarks' => is_string($row['remarks'] ?? null) ? trim($row['remarks']) : ($row['remarks'] ?? null),
                'share_value' => $this->normalizeMoney($row['share_value'] ?? null, true),
                'share_user_id' => $this->normalizeInteger($row['share_user_id'] ?? null),
                'tags' => is_array($row['tags'] ?? null) ? $row['tags'] : [],
            ];
        }, $value);
    }
}
