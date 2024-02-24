<?php

namespace App\Services;

use App\Models\BudgetIncome;
use App\Repositories\Interfaces\{
    BudgetIncomeRepositoryInterface,
    BudgetRepositoryInterface,
    TagRepositoryInterface,
};
use App\Services\Interfaces\BudgetIncomeServiceInterface;
use App\Services\Interfaces\BudgetServiceInterface;
use Exception;
use Illuminate\Support\Collection;

class BudgetIncomeService implements BudgetIncomeServiceInterface
{
    public function __construct(
        private BudgetServiceInterface $budgetService,
        private BudgetRepositoryInterface $budgetRepository,
        private BudgetIncomeRepositoryInterface $budgetIncomeRepository,
        private TagRepositoryInterface $tagRepository,
    ) {
    }

    /**
     * Create a new Income to Budget
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
        $budget = $this->budgetRepository->show($budgetId);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        $income = $this->budgetIncomeRepository->store([
            'description' => $description,
            'date' => $date,
            'value' => $value,
            'remarks' => $remarks,
            'budget_id' => $budgetId
        ]);

        $this->tagRepository->saveTagsToModel($income, $tags);

        // Atualiza Orçamento
        $this->budgetService->recalculateBugdet($budgetId);

        return $income;
    }

    /**
     * Update a new Income to Budget
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
        $income = $this->budgetIncomeRepository->show($id);

        if (!$income) {
            throw new Exception('budget-expense.not-found');
        }

        $budget = $this->budgetRepository->show($income->budget_id);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        // Atualiza Tags
        $this->tagRepository->saveTagsToModel($income, $tags);

        $this->budgetIncomeRepository->store([
            'description' => $description,
            'date' => $date,
            'value' => $value,
            'remarks' => $remarks,
        ], $income);

        // Atualiza Orçamento
        $this->budgetService->recalculateBugdet($income->budget_id);

        return true;
    }

    /**
     * Delete a new Income to Budget
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $income = $this->budgetIncomeRepository->show($id);

        if (!$income) {
            throw new Exception('budget-expense.not-found');
        }

        // Remove Tags
        $this->tagRepository->saveTagsToModel($income);

        // Atualiza Orçamento
        $this->budgetService->recalculateBugdet($income->budget_id);

        return $this->budgetIncomeRepository->delete($id);
    }
}
