<?php

namespace App\Http\Requests\Financing;

class UpdateFinancingRequest extends StoreFinancingRequest
{
    public function rules(): array
    {
        return [
            'description' => ['required', 'string'],
            'start_date' => ['required', 'date_format:Y-m-d'],
            'total' => ['required', 'numeric', 'gt:0'],
            'fees_monthly' => ['required', 'numeric', 'gt:0'],
            'remarks' => ['nullable', 'string'],
            'value_installment' => ['nullable', 'numeric', 'gt:0'],
        ];
    }
}
