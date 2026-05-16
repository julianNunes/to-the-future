<?php

namespace App\Helpers\Budget\Interfaces;

use App\Models\Budget;

interface BudgetRelationPeriodBinderInterface
{
    public function bindUnlinkedRelations(Budget $budget): void;
}
