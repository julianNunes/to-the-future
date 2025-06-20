<?php

namespace App\Repositories;

use App\Models\BudgetExpenseTagOption;
use App\Repositories\Interfaces\BudgetExpenseTagOptionRepositoryInterface;

class BudgetExpenseTagOptionRepository extends AppRepository implements BudgetExpenseTagOptionRepositoryInterface
{
    public function __construct(?BudgetExpenseTagOption $budgetExpenseTagOption = null)
    {
        parent::__construct($budgetExpenseTagOption ?? new BudgetExpenseTagOption);
    }
}
