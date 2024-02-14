<?php

namespace App\Services;

use App\Models\BudgetExpense;
use App\Models\FinancingInstallment;
use Illuminate\Support\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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

        return $expense->update([
            'description' => $description,
            'date' => $date,
            'value' => $value,
            'remarks' => $remarks,
            'paid' => $paid,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
            'financing_installment_id' => $financingInstallmentId,
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
