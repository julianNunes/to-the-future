<?php

namespace App\Services\Interfaces;

use App\Models\BudgetGoal;
use Illuminate\Support\Collection;

interface BudgetGoalServiceInterface
{
    /**
     * Create a new Goal to Budget
     * @param integer $budgetId
     * @param string $description
     * @param float $value
     * @param Collection $tag
     * @param boolean $countShare
     * @param string|null $group
     * @return BudgetGoal
     */
    public function create(
        int $budgetId,
        string $description,
        float $value,
        Collection $tag,
        bool $countShare,
        string $group = null
    ): BudgetGoal;

    /**
     * Update a new Goal to Budget
     * @param integer $id
     * @param string $description
     * @param float $value
     * @param boolean $countShare
     * @param Collection $tag
     * @param string|null $group
     * @return BudgetGoal
     */
    public function update(
        int $id,
        string $description,
        float $value,
        Collection $tag,
        bool $countShare,
        string $group = null
    ): BudgetGoal;

    /**
     * Delete a new Goal to Budget
     * @param int $id
     */
    public function delete(int $id): bool;
}
