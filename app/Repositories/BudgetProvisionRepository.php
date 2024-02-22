<?php

namespace App\Repositories;

use App\Models\BudgetProvision;
use App\Repositories\Interfaces\BudgetProvisionRepositoryInterface;

class BudgetProvisionRepository extends AppRepository implements BudgetProvisionRepositoryInterface
{
    public function __construct(?BudgetProvision $budgetProvision = null)
    {
        parent::__construct($budgetProvision ?? new BudgetProvision);
    }
}
