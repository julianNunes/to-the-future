<?php

namespace App\Http\Requests\Provision;

use Illuminate\Foundation\Http\FormRequest;

class StoreProvisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'description' => is_string($this->description) ? trim($this->description) : $this->description,
            'value' => $this->normalizeMoney($this->value),
            'group' => is_string($this->group) ? trim($this->group) : $this->group,
            'remarks' => is_string($this->remarks) ? trim($this->remarks) : $this->remarks,
            'share_value' => $this->normalizeMoney($this->share_value, true),
            'share_user_id' => $this->normalizeInteger($this->share_user_id),
            'tags' => is_array($this->tags) ? $this->tags : [],
        ]);
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string'],
            'value' => ['required', 'numeric', 'gt:0'],
            'group' => ['required', 'string'],
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
}
