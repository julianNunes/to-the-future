<?php

namespace App\Services\Interfaces;

use App\Models\FixExpense;
use Illuminate\Support\Collection;

interface FixExpenseServiceInterface
{
    /**
     *  Returns data for the Fixed Expense index
     * @return Array
     */
    public function index(): array;

    /**
     * Create a new Fix Expense
     * @param string $description
     * @param string $dueDate
     * @param float $value
     * @param string|null $remarks
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @return FixExpense
     */
    public function create(
        string $description,
        string $dueDate,
        float $value,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null,
    ): FixExpense;

    /**
     * Update a Fix Expense
     * @param integer $id
     * @param string $description
     * @param string $dueDate
     * @param float $value
     * @param string|null $remarks
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @return FixExpense
     */
    public function update(
        int $id,
        string $description,
        string $dueDate,
        float $value,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null,
    ): FixExpense;

    /**
     * Deleta a Fix Expense
     * @param int $id
     */
    public function delete(int $id): bool;
}
