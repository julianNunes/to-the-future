<?php

namespace App\Http\Requests\CreditCardInvoice;

class UpdateCreditCardInvoiceRequest extends StoreCreditCardInvoiceRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'closed' => $this->normalizeBoolean($this->closed),
        ]);
    }

    public function rules(): array
    {
        return [
            'closed' => ['required', 'boolean'],
        ];
    }
}
