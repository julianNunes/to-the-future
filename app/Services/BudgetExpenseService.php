<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\BudgetExpense;
use App\Models\FinancingInstallment;
use Illuminate\Support\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BudgetExpenseService
{
    // public function __construct(private BudgetService $budgetService)
    public function __construct()
    {
    }

    /**
     * Cria uma nova Despesa do Orçamento
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
        $budget = Budget::find($budgetId);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        $expense = BudgetExpense::create([
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

        $expense->save();
        TagService::saveTagsToModel($expense, $tags);

        // Atualizar Parcela do Finaciamento
        if ($expense->paid && $expense->financing_installment_id) {
            $installment = FinancingInstallment::find($expense->financing_installment_id);

            if (!$installment) {
                throw new Exception('financing-installment.not-found');
            }

            $installment->update([
                'paid' => $paid,
                'payment_date' => Carbon::parse($date)->format('y-m-d'),
                'paid_value' => $value,
            ]);
        }

        // Atualiza Orçamento
        BudgetService::recalculateBugdet($budgetId);

        // Se houver valor compartilhado, atualizado dados do Orçamento
        if ($shareUserId) {
            $budget_share = Budget::where(['year' => $budget->year, 'month' => $budget->month, 'user_id' => $shareUserId])->first();

            if ($budget_share) {
                BudgetService::recalculateBugdet($budget_share->id);
            }
        }

        return $expense;
    }

    /**
     * Atualiza a Despesa do Orçamento
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
        $expense = BudgetExpense::with('tags')->find($id);

        if (!$expense) {
            throw new Exception('budget-expense.not-found');
        }

        $budget = Budget::find($expense->budget_id);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        // Atualiza Tags
        TagService::saveTagsToModel($expense, $tags);

        // Atualizar Parcela do Finaciamento
        if ($expense->paid && $expense->financing_installment_id) {
            Log::info('esta pago');
            $installment = FinancingInstallment::find($expense->financing_installment_id);

            if (!$installment) {
                throw new Exception('financing-installment.not-found');
            }

            $installment->update([
                'paid' => $paid,
                'payment_date' => Carbon::parse($date)->format('y-m-d'),
                'paid_value' => $value,
            ]);
        }

        $expense->update([
            'description' => $description,
            'date' => $date,
            'value' => $value,
            'remarks' => $remarks,
            'paid' => $paid,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
            'financing_installment_id' => $financingInstallmentId,
        ]);

        // Atualiza Orçamento
        BudgetService::recalculateBugdet($expense->budget_id);

        // Se houver valor compartilhado, atualizado dados do Orçamento
        if ($shareUserId) {
            $budget_share = Budget::where(['year' => $budget->year, 'month' => $budget->month, 'user_id' => $shareUserId])->first();

            if ($budget_share) {
                BudgetService::recalculateBugdet($budget_share->id);
            }
        }

        return true;
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

        $budget = Budget::find($expense->budget_id);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        // Remove Tags
        TagService::saveTagsToModel($expense);

        $budget_id = $expense->budget_id;
        $share_user_id = $expense->share_user_id;
        $expense->delete();

        // Se houver valor compartilhado, atualizado dados do Orçamento
        if ($share_user_id) {
            $budget_share = Budget::where(['year' => $budget->year, 'month' => $budget->month, 'user_id' => $share_user_id])->first();

            if ($budget_share) {
                BudgetService::recalculateBugdet($budget_share->id);
            }
        }

        // Atualiza Orçamento
        BudgetService::recalculateBugdet($budget_id);

        return true;
    }
}
