<?php

namespace App\Http\Requests\BudgetProvision;

class UpdateBudgetProvisionRequest extends StoreBudgetProvisionRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'budget_id' => ['nullable', 'integer'],
        ];
    }
}
