<?php

namespace App\Http\Requests\PrepaidCard;

use Illuminate\Foundation\Http\FormRequest;

class StorePrepaidCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'digits' => is_string($this->digits) ? trim($this->digits) : $this->digits,
            'is_active' => $this->normalizeBoolean($this->is_active),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'digits' => ['required', 'string', 'size:4', 'regex:/^\d+$/'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function normalizeBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
