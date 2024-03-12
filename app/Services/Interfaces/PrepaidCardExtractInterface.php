<?php

namespace App\Services\Interfaces;

use App\Models\PrepaidCardExtract;

interface PrepaidCardExtractServiceInterface
{

    /**
     * Returns data for Prepaid Card Extract Management
     * @return Array
     */
    public function index(int $prepaidCardId): array;

    /**
     * Create a new Extract
     * @param integer $prepaidCardId
     * @param string $year
     * @param string $month
     * @param float $balance
     * @param string|null $remarks
     * @return PrepaidCardExtract
     */
    public function create(
        int $prepaidCardId,
        string $year,
        string $month,
        float $balance,
        string $remarks = null,
    ): PrepaidCardExtract;

    /**
     * Update a  Extract
     * @param integer $id
     * @param float $balance
     * @param string|null $remarks
     * @return bool
     */
    public function update(
        int $id,
        float $balance,
        string $remarks = null
    ): PrepaidCardExtract;

    /**
     * Delete a Extract
     * @param int $id
     */
    public function delete(int $id): bool;

    /**
     * Returns data for viewing/editing a Prepaid Card Extract
     * @param int $id
     * @return Array
     */
    public function show(int $id): array;
}
