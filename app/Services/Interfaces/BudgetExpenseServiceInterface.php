<?php

namespace App\Services\Interfaces;

use App\Models\BudgetExpense;
use Illuminate\Support\Collection;

interface BudgetExpenseServiceInterface
{
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
    ): BudgetExpense;

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
    ): bool;

    /**
     * Deleta a Expense to Budget
     * @param int $id
     * @return boolean
     */
    public function delete(int $id): bool;
}
