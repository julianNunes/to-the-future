<?php

namespace App\Services\Interfaces;

use App\Models\PrepaidCard;

interface PrepaidCardServiceInterface
{
    /**
     * Returns data to Prepaid Card Management
     * @return Array
     */
    public function index(): array;

    /**
     * Create new Prepaid Card
     * @param string $name
     * @param string $digits
     * @param boolean $isActive
     * @return PrepaidCard
     */
    public function create(
        string $name,
        string $digits,
        bool $isActive
    ): PrepaidCard;

    /**
     * Update a Prepaid Card
     * @param integer $id
     * @param string $name
     * @param string $digits
     * @param boolean $is_active
     * @return boolean
     */
    public function update(
        int $id,
        string $name,
        string $digits,
        bool $isActive
    ): PrepaidCard;

    /**
     * Deleta a Prepaid Card
     * @param int $id
     */
    public function delete(int $id): bool;
}
