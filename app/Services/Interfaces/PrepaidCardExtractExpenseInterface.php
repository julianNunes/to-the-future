<?php

namespace App\Services\Interfaces;

use App\Models\PrepaidCardExtractExpense;
use Illuminate\Support\Collection;

interface PrepaidCardExtractExpenseServiceInterface
{

    /**
     * Create new Expense to Extract
     * @param integer $prepaidCardId
     * @param integer $extractId
     * @param string $description
     * @param string $date
     * @param float $value
     * @param string $group
     * @param string|null $remarks
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @return PrepaidCardExtractExpense
     */
    public function create(
        int $prepaidCardId,
        int $extractId,
        string $description,
        string $date,
        float $value,
        string $group,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null
    ): PrepaidCardExtractExpense;


    /**
     * Update a Expense
     * @param integer $id
     * @param integer $prepaidCardId
     * @param integer $extractId
     * @param string $description
     * @param string $date
     * @param float $value
     * @param string $group
     * @param string|null $remarks
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @return PrepaidCardExtractExpense
     */
    public function update(
        int $id,
        int $prepaidCardId,
        int $extractId,
        string $description,
        string $date,
        float $value,
        string $group,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null
    ): PrepaidCardExtractExpense;

    /**
     * Delete a Expense
     * @param int $id
     */
    public function delete(int $id): bool;
}
