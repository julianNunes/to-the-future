<?php

namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;

interface PeopleRepositoryInterface extends AppRepositoryInterface
{
    public function paginateIndex(?string $search = null, ?array $sort = null, int $limit = 10): LengthAwarePaginator;
}