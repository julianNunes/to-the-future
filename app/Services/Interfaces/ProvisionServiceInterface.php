<?php

namespace App\Services\Interfaces;

use App\Models\Provision;
use Illuminate\Support\Collection;

interface ProvisionServiceInterface
{
    /**
     * Returns data to Provision Management
     * @return Array
     */
    public function index(): array;

    /**
     * Create a new Provision
     * @param string $description
     * @param float $value
     * @param string $group
     * @param string $remarks
     * @param integer $shareValue
     * @param integer|null $shareUserId
     * @param Collection $tags
     * @return Provision
     */
    public function create(
        string $description,
        float $value,
        string $group,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags
    ): Provision;

    /**
     * Update a Provision
     * @param int $id
     * @param string $description
     * @param float $value
     * @param string $group
     * @param string $remarks
     * @param integer $shareValue
     * @param integer|null $shareUserId
     * @param Collection $tags
     * @return Provision
     */
    public function update(
        int $id,
        string $description,
        float $value,
        string $group,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags
    ): Provision;

    /**
     * Delete a Provision
     * @param int $id
     */
    public function delete(int $id): bool;
}
