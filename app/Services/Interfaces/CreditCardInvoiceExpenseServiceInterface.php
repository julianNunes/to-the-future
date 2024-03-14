<?php

namespace App\Services\Interfaces;

use App\Models\CreditCardInvoiceExpense;
use Illuminate\Support\Collection;

interface CreditCardInvoiceExpenseServiceInterface
{
    /**
     * Create new Expense to Invoice and your portions
     * @param integer $creditCardId
     * @param integer $invoiceId
     * @param string $description
     * @param string $date
     * @param float $value
     * @param string $group
     * @param integer|null $portion
     * @param integer|null $portionTotal
     * @param string|null $remarks
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @param Collection|null $divisions
     * @return CreditCardInvoiceExpense
     */
    public function createWithPortions(
        int $creditCardId,
        int $invoiceId,
        string $description,
        string $date,
        float $value,
        string $group,
        int $portion,
        int $portionTotal,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null,
        Collection $divisions = null
    ): CreditCardInvoiceExpense;

    /**
     * Create new Expense to Invoice
     * @param integer $creditCardId
     * @param integer $invoiceId
     * @param string $description
     * @param string $date
     * @param float $value
     * @param string $group
     * @param integer|null $portion
     * @param integer|null $portionTotal
     * @param string|null $remarks
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @param Collection|null $divisions
     * @return CreditCardInvoiceExpense
     */
    public function create(
        int $creditCardId,
        int $invoiceId,
        string $description,
        string $date,
        float $value,
        string $group,
        int $portion = null,
        int $portionTotal = null,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null,
        Collection $divisions = null
    ): CreditCardInvoiceExpense;

    /**
     * Update a Expense
     * @param integer $id
     * @param integer $creditCardId
     * @param integer $invoiceId
     * @param string $description
     * @param string $date
     * @param float $value
     * @param string $group
     * @param integer|null $portion
     * @param integer|null $portionTotal
     * @param string|null $remarks
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @param Collection|null $divisions
     * @return CreditCardInvoiceExpense
     */
    public function update(
        int $id,
        int $creditCardId,
        int $invoiceId,
        string $description,
        string $date,
        float $value,
        string $group,
        int $portion = null,
        int $portionTotal = null,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null,
        Collection $divisions = null,
    ): CreditCardInvoiceExpense;

    /**
     * Delete a Expense
     * @param int $id
     */
    public function delete(int $id): bool;

    /**
     * Delete all Expense portions of a Invoice Credit Card
     * @param int $id
     * @return bool
     */
    public function deletePortions(int $id): bool;

    /**
     * Update Invoice total value
     * @param integer $invoiceId
     * @return void
     */
    public function recalculateTotalInvoice(int $invoiceId);

    /**
     *
     * @param integer $id
     * @param Collection $data
     * @return boolean
     */
    public function storeImportExcel(int $id, Collection $data): bool;
}
