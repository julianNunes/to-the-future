<?php

namespace App\Services\Interfaces;

use App\Models\Budget;

interface BudgetServiceInterface
{

    /**
     * Return data to view Budget
     * @param string $year
     * @return array
     */
    public function index(string $year): array;

    /**
     * Create Budget with your relations
     * @param integer $userId
     * @param string $year
     * @param string $month
     * @param boolean $automaticGenerateYear
     * @param boolean $includeFixExpense
     * @param boolean $includeProvision
     * @return Budget
     */
    public function createComplete(
        int $userId,
        string $year,
        string $month,
        bool $automaticGenerateYear = false,
        bool $includeFixExpense = false,
        bool $includeProvision = false
    ): Budget;

    /**
     * Create a new Budget
     * @param integer $userId
     * @param string $year
     * @param string $month
     * @return Budget
     */
    public function create(
        int $userId,
        string $year,
        string $month
    ): Budget;

    /**
     * Clone a Budget with your relations
     * @param integer $id
     * @param string $year
     * @param string $month
     * @param boolean $includeProvision
     * @param boolean $cloneBugdetExpense
     * @param boolean $cloneBugdetIncome
     * @param boolean $cloneBugdetGoals
     * @return Budget
     */
    public function clone(
        int $id,
        string $year,
        string $month,
        bool $includeProvision = false,
        bool $cloneBugdetExpense = false,
        bool $cloneBugdetIncome = false,
        bool $cloneBugdetGoals = false
    ): Budget;

    /**
     * Update a Budget
     * @param integer $id
     * @param boolean $closed
     * @return boolean
     */
    public function update(
        int $id,
        bool $closed
    ): Budget;

    /**
     * Delete a Budget
     * @param integer $id
     * @return boolean
     */
    public function delete(int $id): bool;

    /**
     * Recalculate the totals to Budget
     * @param integer $id
     * @return Budget
     */
    public function recalculateBugdet(int $id): Budget;

    /**
     * Find a Budget by year and month
     * @param string $year
     * @param string $month
     * @return Budget
     */
    public function findByYearMonth(string $year, string $month): Budget;

    /**
     * Show data to the view
     * @param integer $id
     * @return array
     */
    public function show(int $id): array;
}
