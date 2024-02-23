<?php

namespace App\Repositories;

use App\Models\CreditCardInvoiceExpenseDivision;
use App\Repositories\Interfaces\CreditCardInvoiceExpenseDivisionRepositoryInterface;

class CreditCardInvoiceExpenseDivisionRepository extends AppRepository implements CreditCardInvoiceExpenseDivisionRepositoryInterface
{
    public function __construct(?CreditCardInvoiceExpenseDivision $creditCardInvoiceExpenseDivision = null)
    {
        parent::__construct($creditCardInvoiceExpenseDivision ?? new CreditCardInvoiceExpenseDivision);
    }

    /**
     * Delete all divisions in Expense
     * @param integer $expenseId
     * @return bool
     */
    public function deleteDivisions(int $expenseId): bool
    {
        return $this->model->where('expense_id', $expenseId)->delete();
    }
}
