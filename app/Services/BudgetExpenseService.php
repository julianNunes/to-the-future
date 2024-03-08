<?php

namespace App\Services;

use App\Helpers\Budget\Interfaces\BudgetCalculateInterface;
use App\Models\BudgetExpense;
use App\Repositories\Interfaces\{
    BudgetExpenseRepositoryInterface,
    BudgetRepositoryInterface,
    FinancingInstallmentRepositoryInterface,
    TagRepositoryInterface,
};
use App\Services\Interfaces\BudgetExpenseServiceInterface;
use Illuminate\Support\Carbon;
use Exception;
use Illuminate\Support\Collection;

class BudgetExpenseService implements BudgetExpenseServiceInterface
{
    public function __construct(
        private BudgetRepositoryInterface $budgetRepository,
        private BudgetExpenseRepositoryInterface $budgetExpenseRepository,
        private FinancingInstallmentRepositoryInterface $financingInstallmentRepository,
        private TagRepositoryInterface $tagRepository,
        private BudgetCalculateInterface $budgetCalculate
    ) {
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
     * @param integer|null $financingInstallmentId
     * @param Collection|null $tags
     * @return BudgetExpense
     */
    public function create(
        int $budgetId,
        string $description,
        string $date,
        float $value,
        string $group,
        string $remarks = null,
        bool $paid = false,
        float $shareValue = null,
        int $shareUserId = null,
        int $financingInstallmentId = null,
        Collection $tags = null
    ): BudgetExpense {
        $budget = $this->budgetRepository->show($budgetId);

        if (!$budget) {
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
            'financing_installment_id' => $financingInstallmentId,
            'budget_id' => $budgetId
        ]);

        // Atualiza Tags
        $this->tagRepository->saveTagsToModel($expense, $tags);

        // Atualizar Parcela do Finaciamento
        if ($expense->paid && $expense->financing_installment_id) {
            $installment = $this->financingInstallmentRepository->show($expense->financing_installment_id);

            if (!$installment) {
                throw new Exception('financing-installment.not-found');
            }

            $this->financingInstallmentRepository->store([
                'paid' => $paid,
                'payment_date' => Carbon::parse($date)->format('y-m-d'),
                'paid_value' => $value,
            ], $installment);
        }

        // Atualiza Orçamento
        $this->budgetCalculate->recalculate($budgetId, $shareUserId ? true : false);

        return $expense;
    }

    /**
     * Update a new Expense to Budget
     * @param integer $id
     * @param string $description
     * @param string $date
     * @param float $value
     * @param string $group
     * @param string|null $remarks
     * @param bool|false $paid
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param integer|null $financingInstallmentId
     * @param Collection|null $tags
     * @return boolean
     */
    public function update(
        int $id,
        string $description,
        string $date,
        float $value,
        string $group,
        string $remarks = null,
        bool $paid = false,
        float $shareValue = null,
        int $shareUserId = null,
        int $financingInstallmentId = null,
        Collection $tags = null
    ): bool {
        $expense = $this->budgetExpenseRepository->show($id);

        if (!$expense) {
            throw new Exception('budget-expense.not-found');
        }

        $budget = $this->budgetRepository->show($expense->budget_id);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        $change_share_user = ($shareUserId != $expense->share_user_id) || ($shareValue != $expense->share_value) ? true : false;

        // Atualiza Tags
        $this->tagRepository->saveTagsToModel($expense, $tags);

        // Atualizar Parcela do Finaciamento
        if ($expense->paid && $expense->financing_installment_id) {
            $installment = $this->financingInstallmentRepository->show($expense->financing_installment_id);

            if (!$installment) {
                throw new Exception('financing-installment.not-found');
            }

            $this->financingInstallmentRepository->store([
                'paid' => $paid,
                'payment_date' => Carbon::parse($date)->format('y-m-d'),
                'paid_value' => $value,
            ], $installment);
        }

        $this->budgetExpenseRepository->store([
            'description' => $description,
            'date' => $date,
            'value' => $value,
            'group' => $group,
            'remarks' => $remarks,
            'paid' => $paid,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
            'financing_installment_id' => $financingInstallmentId,
        ], $expense);

        // Atualiza Orçamento
        $this->budgetCalculate->recalculate($expense->budget_id, $change_share_user);

        return true;
    }

    /**
     * Deleta a Expense to Budget
     * @param int $id
     * @return boolean
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

        $this->budgetExpenseRepository->delete($expense->id);

        // Atualiza Orçamento
        $this->budgetCalculate->recalculate($budget_id, $share_user_id ? true : false);

        return true;
    }
}
