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
     * @param string|null $startWeek1
     * @param string|null $endWeek1
     * @param string|null $startWeek2
     * @param string|null $endWeek2
     * @param string|null $startWeek3
     * @param string|null $endWeek3
     * @param string|null $startWeek4
     * @param string|null $endWeek4
     * @param boolean $automaticGenerateYear
     * @param boolean $includeFixExpense
     * @param boolean $includeProvision
     * @return Budget
     */
    public function createComplete(
        int $userId,
        string $year,
        string $month,
        string $startWeek1 = null,
        string $endWeek1 = null,
        string $startWeek2 = null,
        string $endWeek2 = null,
        string $startWeek3 = null,
        string $endWeek3 = null,
        string $startWeek4 = null,
        string $endWeek4 = null,
        bool $automaticGenerateYear = false,
        bool $includeFixExpense = false,
        bool $includeProvision = false
    ): Budget;

    /**
     * Create a new Budget
     * @param integer $userId
     * @param string $year
     * @param string $month
     * @param string|null $startWeek1
     * @param string|null $endWeek1
     * @param string|null $startWeek2
     * @param string|null $endWeek2
     * @param string|null $startWeek3
     * @param string|null $endWeek3
     * @param string|null $startWeek4
     * @param string|null $endWeek4
     * @return Budget
     */
    public function create(
        int $userId,
        string $year,
        string $month,
        string $startWeek1 = null,
        string $endWeek1 = null,
        string $startWeek2 = null,
        string $endWeek2 = null,
        string $startWeek3 = null,
        string $endWeek3 = null,
        string $startWeek4 = null,
        string $endWeek4 = null
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
     * @param string|null $startWeek1
     * @param string|null $endWeek1
     * @param string|null $startWeek2
     * @param string|null $endWeek2
     * @param string|null $startWeek3
     * @param string|null $endWeek3
     * @param string|null $startWeek4
     * @param string|null $endWeek4
     * @param boolean $closed
     * @return boolean
     */
    public function update(
        int $id,
        string $startWeek1 = null,
        string $endWeek1 = null,
        string $startWeek2 = null,
        string $endWeek2 = null,
        string $startWeek3 = null,
        string $endWeek3 = null,
        string $startWeek4 = null,
        string $endWeek4 = null,
        bool $closed = false
    ): Budget;

    /**
     * Delete a Budget
     * @param integer $id
     * @return boolean
     */
    public function delete(int $id): bool;

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

    /**
     * Include in Budget all the Fix Expenses
     * @param integer $id
     * @return boolean
     */
    public function includeFixExpenses(int $id): bool;

    /**
     * Include in Budget all the default Provisions
     * @param integer $id
     * @return boolean
     */
    public function includeProvisions(int $id): bool;
}
