<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\BudgetExpense;
use App\Repositories\Interfaces\BudgetExpenseRepositoryInterface;
use App\Repositories\Interfaces\BudgetRepositoryInterface;
use App\Repositories\Interfaces\FinancingInstallmentRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Services\Interfaces\BudgetExpenseServiceInterface;
use App\Services\Interfaces\BudgetServiceInterface;
use Illuminate\Support\Carbon;
use Exception;
use Illuminate\Support\Collection;

class BudgetExpenseService implements BudgetExpenseServiceInterface
{
    public function __construct(
        private BudgetServiceInterface $budgetService,
        private BudgetRepositoryInterface $budgetRepository,
        private BudgetExpenseRepositoryInterface $budgetExpenseRepository,
        private FinancingInstallmentRepositoryInterface $financingInstallmentRepository,
        private TagRepositoryInterface $tagRepository,
    ) {
    }

    /**
     * Create a new Expense to Budget
     * @param string $description
     * @param string $date
     * @param float $value
     * @param string $remarks
     * @param bool $paid
     * @param integer $budgetId
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param integer|null $financingInstallmentId
     * @param Collection|null $tags
     * @return BudgetExpense
     */
    public function create(
        string $description,
        string $date,
        float $value,
        string $remarks,
        bool $paid,
        int $budgetId,
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
        /**
         * @todo TROCAR POSTERIORMENTE
         */
        BudgetService::recalculateBugdet($budgetId);

        // Se houver valor compartilhado, atualizado dados do Orçamento
        if ($shareUserId) {
            $budget_share = $this->budgetRepository->getOne(['year' => $budget->year, 'month' => $budget->month, 'user_id' => $shareUserId]);

            if ($budget_share) {
                /**
                 * @todo TROCAR POSTERIORMENTE
                 */
                BudgetService::recalculateBugdet($budget_share->id);
            }
        }

        return $expense;
    }

    /**
     * Update a new Expense to Budget
     * @param integer $id
     * @param string $description
     * @param string $date
     * @param float $value
     * @param string $remarks
     * @param bool $paid
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
        string $remarks,
        bool $paid,
        float $shareValue = null,
        int $shareUserId = null,
        int $financingInstallmentId = null,
        Collection $tags = null
    ): bool {
        $expense = $this->budgetExpenseRepository->show($id, ['tags']);

        if (!$expense) {
            throw new Exception('budget-expense.not-found');
        }

        $budget = $this->budgetRepository->show($expense->budget_id);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

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
            'remarks' => $remarks,
            'paid' => $paid,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
            'financing_installment_id' => $financingInstallmentId,
        ], $expense);

        // Atualiza Orçamento
        /**
         * @todo TROCAR POSTERIORMENTE
         */
        BudgetService::recalculateBugdet($expense->budget_id);

        // Se houver valor compartilhado, atualizado dados do Orçamento
        if ($shareUserId) {
            $budget_share = $this->budgetRepository->getOne(['year' => $budget->year, 'month' => $budget->month, 'user_id' => $shareUserId]);

            if ($budget_share) {
                /**
                 * @todo TROCAR POSTERIORMENTE
                 */
                BudgetService::recalculateBugdet($budget_share->id);
            }
        }

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

        // Se houver valor compartilhado, atualizado dados do Orçamento
        if ($share_user_id) {
            $budget_share = $this->budgetRepository->getOne(['year' => $budget->year, 'month' => $budget->month, 'user_id' => $share_user_id]);

            if ($budget_share) {
                /**
                 * @todo TROCAR POSTERIORMENTE
                 */
                BudgetService::recalculateBugdet($budget_share->id);
            }
        }

        // Atualiza Orçamento
        /**
         * @todo TROCAR POSTERIORMENTE
         */
        BudgetService::recalculateBugdet($budget_id);

        return true;
    }
}
