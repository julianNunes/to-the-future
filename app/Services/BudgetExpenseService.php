<?php

namespace App\Services;

use App\Models\BudgetExpense;
use Illuminate\Support\Carbon;
use Exception;
use Illuminate\Support\Collection;

class BudgetExpenseService
{
    public function __construct()
    {
    }

    /**
     * Cria uma nova Despesa do Orçamento
     * @param string $description
     * @param string $date
     * @param float $value
     * @param string $remarks
     * @param integer $budgetId
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @return BudgetExpense
     */
    public function create(
        string $description,
        string $date,
        float $value,
        string $remarks,
        int $budgetId,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null
    ): BudgetExpense {
        $expense = BudgetExpense::create([
            'description' => $description,
            'date' => $date,
            'value' => $value,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
            'budget_id' => $budgetId
        ]);

        $expense->save();
        TagService::saveTagsToModel($expense, $tags);

        return $expense;
    }

    /**
     * Atualiza a Despesa do Orçamento
     * @param integer $id
     * @param string $description
     * @param string $date
     * @param float $value
     * @param string $remarks
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @return boolean
     */
    public function update(
        int $id,
        string $description,
        string $date,
        float $value,
        string $remarks,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null
    ): bool {
        $expense = BudgetExpense::with('tags')->find($id);

        if (!$expense) {
            throw new Exception('budget-expense.not-found');
        }

        // Atualiza Tags
        TagService::saveTagsToModel($expense, $tags);

        return $expense->update([
            'description' => $description,
            'date' => $date,
            'value' => $value,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
        ]);
    }

    /**
     * Deleta uma Despesa do Orçamento
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $expense = BudgetExpense::with('tags')->find($id);

        if (!$expense) {
            throw new Exception('budget-expense.not-found');
        }

        // Remove Tags
        TagService::saveTagsToModel($expense);

        return $expense->delete();
    }
}
