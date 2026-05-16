<?php

namespace App\Http\Requests\Financing;

use Illuminate\Validation\Rule;

class UpdateFinancingInstallmentRequest extends StoreFinancingRequest
{
    protected function prepareForValidation(): void
    {
        $paid = $this->normalizeBoolean($this->paid);

        $this->merge([
            'date' => $this->normalizeDate($this->date),
            'value' => $this->normalizeMoney($this->value),
            'paid' => $paid,
            'payment_date' => $paid === false ? null : $this->normalizeDate($this->payment_date),
            'paid_value' => $paid === false ? null : $this->normalizeMoney($this->paid_value),
        ]);
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'value' => ['required', 'numeric', 'gt:0'],
            'paid' => ['required', 'boolean'],
            'payment_date' => [Rule::requiredIf(fn () => $this->boolean('paid')), 'nullable', 'date_format:Y-m-d'],
            'paid_value' => [Rule::requiredIf(fn () => $this->boolean('paid')), 'nullable', 'numeric', 'gt:0'],
        ];
    }

    protected function normalizeBoolean(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) && in_array($value, [0, 1], true)) {
            return (bool) $value;
        }

        if (! is_string($value)) {
            return $value;
        }

        $normalized = strtolower(trim($value));

        if (in_array($normalized, ['1', 'true', 'on', 'yes'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'off', 'no'], true)) {
            return false;
        }

        return $value;
    }
}
