<?php

namespace App\Repositories;

use App\Models\BudgetGoal;
use App\Repositories\Interfaces\BudgetGoalRepositoryInterface;

class BudgetGoalRepository extends AppRepository implements BudgetGoalRepositoryInterface
{
    public function __construct(?BudgetGoal $budgetGoal = null)
    {
        parent::__construct($budgetGoal ?? new BudgetGoal);
    }
}
