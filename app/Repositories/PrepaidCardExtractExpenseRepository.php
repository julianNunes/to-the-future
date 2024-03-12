<?php

namespace App\Repositories;

use App\Models\PrepaidCardExtractExpense;
use App\Repositories\Interfaces\PrepaidCardExtractExpenseRepositoryInterface;

class PrepaidCardExtractExpenseRepository extends AppRepository implements PrepaidCardExtractExpenseRepositoryInterface
{
    public function __construct(?PrepaidCardExtractExpense $prepaidCardExtractExpense = null)
    {
        parent::__construct($prepaidCardExtractExpense ?? new PrepaidCardExtractExpense);
    }
}
