<?php

namespace App\Repositories;

use App\Models\CreditCardInvoiceExpense;
use App\Repositories\Interfaces\CreditCardInvoiceExpenseRepositoryInterface;

class CreditCardInvoiceExpenseRepository extends AppRepository implements CreditCardInvoiceExpenseRepositoryInterface
{
    public function __construct(?CreditCardInvoiceExpense $creditCardInvoiceExpense = null)
    {
        parent::__construct($creditCardInvoiceExpense ?? new CreditCardInvoiceExpense);
    }
}
