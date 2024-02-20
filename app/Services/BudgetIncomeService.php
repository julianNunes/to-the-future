<?php

namespace App\Services;

use App\Models\BudgetIncome;
use Illuminate\Support\Carbon;
use Exception;
use Illuminate\Support\Collection;

class BudgetIncomeService
{
    // public function __construct(private BudgetService $budgetService)
    public function __construct()
    {
    }

    /**
     * Cria uma nova Receita do Orçamento
     * @param string $description
     * @param string $date
     * @param float $value
     * @param string $remarks
     * @param integer $budgetId
     * @param Collection|null $tags
     * @return BudgetIncome
     */
    public function create(
        string $description,
        string $date,
        float $value,
        string $remarks,
        int $budgetId,
        Collection $tags = null
    ): BudgetIncome {
        $income = BudgetIncome::create([
            'description' => $description,
            'date' => $date,
            'value' => $value,
            'remarks' => $remarks,
            'budget_id' => $budgetId
        ]);

        $income->save();
        TagService::saveTagsToModel($income, $tags);

        // Atualiza Orçamento
        BudgetService::recalculateBugdet($budgetId);

        return $income;
    }

    /**
     * Atualiza a Receita do Orçamento
     * @param integer $id
     * @param string $description
     * @param string $date
     * @param float $value
     * @param string $remarks
     * @param Collection|null $tags
     * @return boolean
     */
    public function update(
        int $id,
        string $description,
        string $date,
        float $value,
        string $remarks,
        Collection $tags = null
    ): bool {
        $income = BudgetIncome::with('tags')->find($id);

        if (!$income) {
            throw new Exception('budget-expense.not-found');
        }

        // Atualiza Tags
        TagService::saveTagsToModel($income, $tags);

        $income->update([
            'description' => $description,
            'date' => $date,
            'value' => $value,
            'remarks' => $remarks,
        ]);

        // Atualiza Orçamento
        BudgetService::recalculateBugdet($income->budget_id);

        return true;
    }

    /**
     * Deleta uma Receita do Orçamento
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $income = BudgetIncome::with('tags')->find($id);

        if (!$income) {
            throw new Exception('budget-expense.not-found');
        }

        // Remove Tags
        TagService::saveTagsToModel($income);

        // Atualiza Orçamento
        BudgetService::recalculateBugdet($income->budget_id);

        return $income->delete();
    }
}
