<?php

namespace App\Services\Interfaces;

use App\Models\BudgetProvision;
use Illuminate\Support\Collection;

interface BudgetProvisionServiceInterface
{
    /**
     * Create a new Provision to Budget
     * @param string $description
     * @param float $value
     * @param string $group
     * @param integer $budgetId
     * @param string|null $remarks
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @return BudgetProvision
     */
    public function create(
        string $description,
        float $value,
        string $group,
        int $budgetId,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null
    ): BudgetProvision;

    /**
     * Update a new Provision to Budget
     * @param integer $id
     * @param string $description
     * @param float $value
     * @param string $group
     * @param string|null $remarks
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @return boolean
     */
    public function update(
        int $id,
        string $description,
        float $value,
        string $group,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null
    ): BudgetProvision;

    /**
     * Delete a new Provision to Budget
     * @param int $id
     */
    public function delete(int $id): bool;
}
