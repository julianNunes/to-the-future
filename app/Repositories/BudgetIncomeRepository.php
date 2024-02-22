<?php

namespace App\Repositories;

use App\Models\BudgetIncome;
use App\Repositories\Interfaces\BudgetIncomeRepositoryInterface;

class BudgetIncomeRepository extends AppRepository implements BudgetIncomeRepositoryInterface
{
    public function __construct(?BudgetIncome $budgetIncome = null)
    {
        parent::__construct($budgetIncome ?? new BudgetIncome);
    }
}
