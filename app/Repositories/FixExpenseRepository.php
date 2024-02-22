<?php

namespace App\Repositories;

use App\Models\FixExpense;
use App\Repositories\Interfaces\FixExpenseRepositoryInterface;

class FixExpenseRepository extends AppRepository implements FixExpenseRepositoryInterface
{
    public function __construct(?FixExpense $fixExpense = null)
    {
        parent::__construct($fixExpense ?? new FixExpense);
    }
}
