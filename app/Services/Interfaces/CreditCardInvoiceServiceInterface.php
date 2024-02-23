<?php

namespace App\Services\Interfaces;

use App\Models\CreditCardInvoice;

interface CreditCardInvoiceServiceInterface
{
    /**
     * Returns data for Credit Card Invoice Management
     * @return Array
     */
    public function index(int $creditCardId): array;

    /**
     * Create a new Invoices at to end of year
     * @param string $dueDate
     * @param string $closingDate
     * @param string $year
     * @param string $month
     * @param integer $creditCardId
     * @return CreditCardInvoice
     */
    public function createAutomatic(
        string $dueDate,
        string $closingDate,
        string $year,
        string $month,
        int $creditCardId,
    ): bool;

    /**
     * Create a new Invoice
     * @param string $dueDate
     * @param string $closingDate
     * @param string $year
     * @param string $month
     * @param integer $creditCardId
     * @return CreditCardInvoice
     */
    public function create(
        string $dueDate,
        string $closingDate,
        string $year,
        string $month,
        int $creditCardId,
    ): CreditCardInvoice;

    /**
     * Delete a Invoice
     * @param int $id
     */
    public function delete(int $id): bool;

    /**
     * Returns data for viewing/editing a Credit Card Invoice
     * @param int $id
     * @return Array
     */
    public function show(int $id): array;
}
