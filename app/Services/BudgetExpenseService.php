<?php

namespace App\Services;

use App\Helpers\Budget\Interfaces\BudgetCalculateInterface;
use App\Models\BudgetExpense;
use App\Repositories\Interfaces\{
    BudgetExpenseRepositoryInterface,
    BudgetRepositoryInterface,
    TagRepositoryInterface,
};
use App\Services\Interfaces\BudgetExpenseServiceInterface;
use Exception;
use Illuminate\Support\{Carbon, Collection};
use Str;

class BudgetExpenseService implements BudgetExpenseServiceInterface
{
    public function __construct(
        private BudgetRepositoryInterface $budgetRepository,
        private BudgetExpenseRepositoryInterface $budgetExpenseRepository,
        private TagRepositoryInterface $tagRepository,
        private BudgetCalculateInterface $budgetCalculate
    ) {}

    /**
     * Create a new Expense to Budget with Portions
     * @param integer $budgetId
     * @param string $description
     * @param string $date
     * @param float $value
     * @param int $portion
     * @param int $portionTotal
     * @param string $group
     * @param string|null $remarks
     * @param bool|false $paid
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @return BudgetExpense
     */
    public function createWithPortions(
        int $budgetId,
        string $description,
        string $date,
        float $value,
        int $portion,
        int $portionTotal,
        string $group,
        ?string $remarks = null,
        ?bool $paid = false,
        ?float $shareValue = null,
        ?int $shareUserId = null,
        ?Collection $tags = null
    ): BudgetExpense {
        $budget = $this->budgetRepository->show($budgetId);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        $group_portion = Str::uuid(); // Gera um UUID para o grupo

        $expense = $this->budgetExpenseRepository->store([
            'description' => $description,
            'date' => $date,
            'value' => $value,
            'remarks' => $remarks,
            'group' => $group,
            'group_portion' => $group_portion,
            'portion' => $portion,
            'portion_total' => $portionTotal,
            'paid' => $paid,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
            'budget_id' => $budgetId,
        ]);

        // Atualiza Tags
        $this->tagRepository->saveTagsToModel($expense, $tags);

        // Atualiza Orçamento
        $this->budgetCalculate->recalculate($budgetId, $shareUserId ? true : false);

        // Crio as outras parcelas
        if ($portionTotal && $portionTotal > 1) {
            $date = Carbon::parse($expense->date);
            $new_date = $date->copy()->addMonth();
            $new_date_budget =  Carbon::parse($budget->year . '-' . $budget->month . '-01')->copy()->addMonth();

            for ($i = ($portion + 1); $i <= $portionTotal; $i++) {
                // Busca o Orçamento do mes seguinte
                $new_budget = $this->budgetRepository->getOne(['year' => $new_date_budget->year, 'month' => $new_date_budget->month, 'user_id' => $budget->user_id]);

                // Se não existe, ela é criada
                if (! $new_budget) {
                    $new_budget = $this->budgetRepository->store([
                        'year' => $new_date_budget->year,
                        'month' => $new_date_budget->month,
                        'user_id' => $budget->user_id
                    ]);
                }

                $this->budgetExpenseRepository->store([
                    'description' => $description,
                    'date' => $new_date->format('y-m-d'),
                    'value' => $value,
                    'remarks' => $remarks,
                    'group' => $group,
                    'group_portion' => $group_portion,
                    'portion' => $i,
                    'portion_total' => $portionTotal,
                    'paid' => false,
                    'share_value' => $shareValue,
                    'share_user_id' => $shareUserId,
                    'budget_id' => $new_budget->id,
                ]);

                $new_date->addMonth();
                $new_date_budget->addMonth();

                // Atualiza Orçamento
                $this->budgetCalculate->recalculate($new_budget->id, $shareUserId ? true : false);
            }
        }

        return $expense;
    }

    /**
     * Create a new Expense to Budget
     * @param integer $budgetId
     * @param string $description
     * @param string $date
     * @param float $value
     * @param string $group
     * @param string|null $remarks
     * @param bool|false $paid
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @return BudgetExpense
     */
    public function create(
        int $budgetId,
        string $description,
        string $date,
        float $value,
        string $group,
        ?string $remarks = null,
        ?bool $paid = false,
        ?float $shareValue = null,
        ?int $shareUserId = null,
        ?Collection $tags = null
    ): BudgetExpense {
        $budget = $this->budgetRepository->show($budgetId);

        if (! $budget) {
            throw new Exception('budget.not-found');
        }

        $expense = $this->budgetExpenseRepository->store([
            'description' => $description,
            'date' => $date,
            'value' => $value,
            'remarks' => $remarks,
            'group' => $group,
            'paid' => $paid,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
            'budget_id' => $budgetId,
        ]);

        // Atualiza Tags
        $this->tagRepository->saveTagsToModel($expense, $tags);

        // Atualiza Orçamento
        $this->budgetCalculate->recalculate($budgetId, $shareUserId ? true : false);

        return $expense;
    }

    /**
     * Update a new Expense to Budget
     *
     * @param  bool|false  $paid
     */
    public function update(
        int $id,
        string $description,
        string $date,
        float $value,
        string $group,
        ?string $remarks = null,
        ?bool $paid = false,
        ?float $shareValue = null,
        ?int $shareUserId = null,
        ?Collection $tags = null
    ): bool {
        $expense = $this->budgetExpenseRepository->show($id);

        if (! $expense) {
            throw new Exception('budget-expense.not-found');
        }

        $budget = $this->budgetRepository->show($expense->budget_id);

        if (! $budget) {
            throw new Exception('budget.not-found');
        }

        $change_share_user = ($shareUserId != $expense->share_user_id) || ($shareValue != $expense->share_value) ? true : false;

        // Atualiza Tags
        $this->tagRepository->saveTagsToModel($expense, $tags);

        $this->budgetExpenseRepository->store([
            'description' => $description,
            'date' => $date,
            'value' => $value,
            'group' => $group,
            'remarks' => $remarks,
            'paid' => $paid,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
        ], $expense);

        // Atualiza Orçamento
        $this->budgetCalculate->recalculate($expense->budget_id, $change_share_user);

        return true;
    }

    /**
     * Deleta a Expense to Budget
     */
    public function delete(int $id): bool
    {
        $expense = $this->budgetExpenseRepository->show($id);

        if (!$expense) {
            throw new Exception('budget-expense.not-found');
        }

        $budget = $this->budgetRepository->show($expense->budget_id);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        // Remove Tags
        $this->tagRepository->saveTagsToModel($expense);

        $budget_id = $expense->budget_id;
        $share_user_id = $expense->share_user_id;

        // Remove Despesa do Orçamento
        $this->budgetExpenseRepository->delete($expense->id);

        // Atualiza Orçamento
        $this->budgetCalculate->recalculate($budget_id, $share_user_id ? true : false);

        return true;
    }

    /**
     * Delete all Expenses with Portion from a Budget 
     * @param string $groupPortion
     * @return boolean
     */
    public function deleteAllPortions(string $groupPortion): bool
    {
        $expenses = $this->budgetExpenseRepository->get(['group_portion' => $groupPortion]);

        if ($expenses->count()) {
            throw new Exception('budget-expense.not-found');
        }

        foreach ($expenses as $expense) {
            $budget = $this->budgetRepository->show($expense->budget_id);

            if (!$budget) {
                throw new Exception('budget.not-found');
            }

            // Remove Tags
            $this->tagRepository->saveTagsToModel($expense);

            $budget_id = $expense->budget_id;
            $share_user_id = $expense->share_user_id;

            // Remove Despesa do Orçamento
            $this->budgetExpenseRepository->delete($expense->id);

            // Atualiza Orçamento
            $this->budgetCalculate->recalculate($budget_id, $share_user_id ? true : false);
        }

        return true;
    }
}
