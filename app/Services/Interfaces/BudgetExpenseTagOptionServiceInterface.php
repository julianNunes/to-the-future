<?php

namespace App\Services\Interfaces;

use App\Models\BudgetExpenseTagOption;
use Illuminate\Support\Collection;

interface BudgetExpenseTagOptionServiceInterface
{
    /**
     * Create a new Goal to Budget
     * @param integer $budgetId
     * @param Collection $tags
     * @param boolean $countShare
     * @param string|null $group
     * @return BudgetExpenseTagOption
     */
    public function create(
        int $budgetId,
        Collection $tags,
        bool $countShare,
        ?string $group = null
    ): BudgetExpenseTagOption;

    /**
     * Update a new Goal to Budget
     * @param integer $id
     * @param Collection $tags
     * @param boolean $countShare
     * @param string|null $group
     * @return BudgetExpenseTagOption
     */
    public function update(
        int $id,
        Collection $tags,
        bool $countShare,
        ?string $group = null
    ): BudgetExpenseTagOption;

    /**
     * Delete a new Goal to Budget
     * @param int $id
     */
    public function delete(int $id): bool;
}
