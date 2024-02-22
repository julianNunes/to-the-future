<?php

namespace App\Services\Interfaces;

use App\Models\Financing;
use App\Models\FixExpense;
use Illuminate\Support\Collection;

interface FinancingServiceInterface
{
    /**
     * Returns data to Financial Management
     * @return Array
     */
    public function index(): array;

    /**
     * Create a new Financing amd your installments
     * @param string $description
     * @param string $startDate
     * @param float $total
     * @param float $feesMonthly
     * @param integer $portionTotal
     * @param string $remarks
     * @param string $startDateInstallment
     * @param float $valueInstallment
     * @return Financing
     */
    public function create(
        string $description,
        string $startDate,
        float $total,
        float $feesMonthly,
        int $portionTotal,
        string $startDateInstallment,
        float $valueInstallment,
        string $remarks = null
    ): Financing;

    /**
     * Edit a Financing. Only some data are available.
     * Updating of installments will only occur in installments that are open
     * @param integer $id
     * @param string $description
     * @param string $startDate
     * @param float $total
     * @param float $feesMonthly
     * @param string $remarks
     * @param float|null $valueInstallment
     * @return boolean
     */
    public function update(
        int $id,
        string $description,
        string $startDate,
        float $total,
        float $feesMonthly,
        float $valueInstallment = null,
        string $remarks = null
    ): Financing;

    /**
     * Deleta a Financing
     * @param int $id
     */
    public function delete(int $id): bool;
}
