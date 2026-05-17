<?php

namespace App\Services\Interfaces;

use App\Models\BudgetExpense;
use Illuminate\Support\Collection;

interface BudgetExpenseServiceInterface
{
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
    ): BudgetExpense;

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
     * @param integer|null $
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
    ): BudgetExpense;

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
     * @param Collection|null $tags
     * @return boolean
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
    ): bool;

    /**
     * Deleta a Expense to Budget
     * @param int $id
     * @return boolean
     */
    public function delete(int $id, bool $shouldRecalculate = true): bool;

    /**
     * Delete all Expenses with Portion from a Budget
     * @param string $groupPortion
     * @return boolean
     */
    public function deleteAllPortions(string $groupPortion): bool;
}
