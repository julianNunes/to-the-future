<?php

namespace App\Repositories\Interfaces;

interface CreditCardInvoiceExpenseDivisionRepositoryInterface extends AppRepositoryInterface
{
    /**
     * Delete all divisions in Expense
     * @param integer $expenseId
     * @return bool
     */
    public function deleteDivisions(int $expenseId): bool;
}
