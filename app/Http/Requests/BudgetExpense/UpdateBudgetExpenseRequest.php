<?php

namespace App\Http\Requests\BudgetExpense;

class UpdateBudgetExpenseRequest extends StoreBudgetExpenseRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'budget_id' => ['nullable', 'integer'],
            'paid' => ['nullable', 'boolean'],
        ];
    }
}
