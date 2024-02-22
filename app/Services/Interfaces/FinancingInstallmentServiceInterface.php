<?php

namespace App\Services\Interfaces;

use App\Models\FinancingInstallment;

interface FinancingInstallmentServiceInterface
{
    /**
     * Returns data for Financing Installment Management
     * @return Array
     */
    public function index(int $financingId): array;

    /**
     * Update a Installment of Financing
     * @param integer $id
     * @param string $date
     * @param float $value
     * @param boolean $paid
     * @param string|null $paymentDate
     * @param float|null $paidValue
     * @return boolean
     */
    public function update(
        int $id,
        string $date,
        float $value,
        bool $paid,
        string $paymentDate = null,
        float $paidValue = null,
    ): FinancingInstallment;
}
