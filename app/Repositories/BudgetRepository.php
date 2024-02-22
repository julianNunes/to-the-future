<?php

namespace App\Repositories;

use App\Models\Budget;
use App\Repositories\Interfaces\BudgetRepositoryInterface;

class BudgetRepository extends AppRepository implements BudgetRepositoryInterface
{
    public function __construct(?Budget $budget = null)
    {
        parent::__construct($budget ?? new Budget);
    }
}
