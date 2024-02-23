<?php

namespace App\Services\Interfaces;

use App\Models\BudgetGoal;
use Illuminate\Support\Collection;

interface BudgetGoalServiceInterface
{
    /**
     * Create a new Goal to Budget
     * @param string $description
     * @param float $value
     * @param float $group
     * @param boolean $countOnlyShare
     * @param integer $budgetId
     * @param Collection|null $tags
     * @return BudgetGoal
     */
    public function create(
        string $description,
        float $value,
        float $group,
        bool $countOnlyShare,
        int $budgetId,
        Collection $tags = null
    ): BudgetGoal;

    /**
     * Update a new Goal to Budget
     * @param integer $id
     * @param string $description
     * @param float $value
     * @param string $group
     * @param boolean $countOnlyShare
     * @param Collection|null $tags
     * @return boolean
     */
    public function update(
        int $id,
        string $description,
        float $value,
        string $group,
        bool $countOnlyShare,
        Collection $tags = null
    ): BudgetGoal;

    /**
     * Delete a new Goal to Budget
     * @param int $id
     */
    public function delete(int $id): bool;
}
