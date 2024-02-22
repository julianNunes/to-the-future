<?php

namespace App\Repositories;

use App\Models\BudgetExpense;
use App\Repositories\Interfaces\BudgetExpenseRepositoryInterface;

class BudgetExpenseRepository extends AppRepository implements BudgetExpenseRepositoryInterface
{
    public function __construct(?BudgetExpense $budgetExpense = null)
    {
        parent::__construct($budgetExpense ?? new BudgetExpense);
    }
}
