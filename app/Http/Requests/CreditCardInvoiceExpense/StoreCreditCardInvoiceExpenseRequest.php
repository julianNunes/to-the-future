<?php

namespace App\Http\Requests\CreditCardInvoiceExpense;

use Illuminate\Foundation\Http\FormRequest;

class StoreCreditCardInvoiceExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'credit_card_id' => $this->normalizeInteger($this->credit_card_id),
            'invoice_id' => $this->normalizeInteger($this->invoice_id),
            'description' => is_string($this->description) ? trim($this->description) : $this->description,
            'date' => is_string($this->date) ? trim($this->date) : $this->date,
            'value' => $this->normalizeMoney($this->value),
            'group' => is_string($this->group) ? trim($this->group) : $this->group,
            'portion' => $this->normalizeInteger($this->portion),
            'portion_total' => $this->normalizeInteger($this->portion_total),
            'remarks' => is_string($this->remarks) ? trim($this->remarks) : $this->remarks,
            'share_value' => $this->normalizeMoney($this->share_value, true),
            'share_user_id' => $this->normalizeInteger($this->share_user_id),
            'tags' => is_array($this->tags) ? $this->tags : [],
            'divisions' => $this->normalizeDivisions($this->divisions),
        ]);
    }

    public function rules(): array
    {
        return [
            'credit_card_id' => ['required', 'integer'],
            'invoice_id' => ['required', 'integer'],
            'description' => ['required', 'string'],
            'date' => ['required', 'date_format:Y-m-d'],
            'value' => ['required', 'numeric', 'gt:0'],
            'group' => ['required', 'string', 'in:PORTION,WEEK_1,WEEK_2,WEEK_3,WEEK_4'],
            'portion' => ['nullable', 'integer', 'min:1', 'required_with:portion_total'],
            'portion_total' => ['nullable', 'integer', 'min:2', 'required_with:portion'],
            'remarks' => ['nullable', 'string'],
            'share_value' => ['nullable', 'numeric', 'gt:0', 'required_with:share_user_id'],
            'share_user_id' => ['nullable', 'integer', 'required_with:share_value'],
            'tags' => ['nullable', 'array'],
            'tags.*.name' => ['required', 'string'],
            'divisions' => ['nullable', 'array'],
            'divisions.*.description' => ['required', 'string'],
            'divisions.*.value' => ['required', 'numeric', 'gt:0'],
            'divisions.*.remarks' => ['nullable', 'string'],
            'divisions.*.share_value' => ['nullable', 'numeric', 'gt:0', 'required_with:divisions.*.share_user_id'],
            'divisions.*.share_user_id' => ['nullable', 'integer', 'required_with:divisions.*.share_value'],
            'divisions.*.tags' => ['nullable', 'array'],
            'divisions.*.tags.*.name' => ['required', 'string'],
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

    protected function normalizeDivisions(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_map(function ($division) {
            if (! is_array($division)) {
                return [];
            }

            return [
                'description' => is_string($division['description'] ?? null) ? trim($division['description']) : ($division['description'] ?? null),
                'value' => $this->normalizeMoney($division['value'] ?? null),
                'remarks' => is_string($division['remarks'] ?? null) ? trim($division['remarks']) : ($division['remarks'] ?? null),
                'share_value' => $this->normalizeMoney($division['share_value'] ?? null, true),
                'share_user_id' => $this->normalizeInteger($division['share_user_id'] ?? null),
                'tags' => is_array($division['tags'] ?? null) ? $division['tags'] : [],
            ];
        }, $value);
    }
}
