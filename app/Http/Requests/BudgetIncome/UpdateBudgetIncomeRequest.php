<?php

namespace App\Http\Requests\BudgetIncome;

class UpdateBudgetIncomeRequest extends StoreBudgetIncomeRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'budget_id' => ['nullable', 'integer'],
        ];
    }
}
