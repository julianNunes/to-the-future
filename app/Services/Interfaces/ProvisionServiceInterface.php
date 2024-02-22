<?php

namespace App\Services\Interfaces;

use App\Models\Provision;
use Illuminate\Support\Collection;

interface ProvisionServiceInterface
{
    /**
     * Retorna os dados para o index de Provisionamento
     * @return Array
     */
    public function index(): array;

    /**
     * Cria um novo Provisionamento
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
        float $shareValue = 0,
        int $shareUserId = null,
        Collection $tags
    ): Provision;

    /**
     * Atualiza um Provisionamento
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
     * Deleta um Provisionamento
     * @param int $id
     */
    public function delete(int $id): bool;
}
