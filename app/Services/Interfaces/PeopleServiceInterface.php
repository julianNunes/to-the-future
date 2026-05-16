<?php

namespace App\Services\Interfaces;

use App\Models\People;

interface PeopleServiceInterface
{
    public function index(?string $search = null, ?array $sort = null, int $limit = 10): array;

    public function show(int $id): People;

    public function create(array $data): People;

    public function update(int $id, array $data): People;

    public function delete(int $id): bool;
}
