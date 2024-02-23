<?php

namespace App\Services\Interfaces;

use App\Models\BudgetIncome;
use Illuminate\Support\Collection;

interface BudgetIncomeServiceInterface
{
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
    ): BudgetIncome;

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
    ): bool;

    /**
     * Delete a new Income to Budget
     * @param int $id
     */
    public function delete(int $id): bool;
}
