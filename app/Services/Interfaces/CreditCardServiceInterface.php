<?php

namespace App\Services\Interfaces;

use App\Models\CreditCard;

interface CreditCardServiceInterface
{
    /**
     * Returns data to Credit Card Management
     * @return Array
     */
    public function index(): array;

    /**
     * Create new Credit Card
     * @param string $name
     * @param string $digits
     * @param string $dueDate
     * @param string $closingDate
     * @param boolean $isActive
     * @return CreditCard
     */
    public function create(
        string $name,
        string $digits,
        string $dueDate,
        string $closingDate,
        bool $isActive
    ): CreditCard;

    /**
     * Update a Credit Card
     * @param integer $id
     * @param string $name
     * @param string $digits
     * @param string $due_date
     * @param string $closing_date
     * @param boolean $is_active
     * @return boolean
     */
    public function update(
        int $id,
        string $name,
        string $digits,
        string $dueDate,
        string $closingDate,
        bool $isActive
    ): CreditCard;

    /**
     * Deleta a Credit Card
     * @param int $id
     */
    public function delete(int $id): bool;
}
