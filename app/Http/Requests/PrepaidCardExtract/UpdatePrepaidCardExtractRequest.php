<?php

namespace App\Http\Requests\PrepaidCardExtract;

class UpdatePrepaidCardExtractRequest extends StorePrepaidCardExtractRequest
{
    public function rules(): array
    {
        return [
            'credit' => ['required', 'numeric', 'gt:0'],
            'credit_date' => ['required', 'date_format:Y-m-d'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
