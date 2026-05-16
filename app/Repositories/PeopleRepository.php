<?php

namespace App\Repositories;

use App\Models\People;
use App\Repositories\Interfaces\PeopleRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class PeopleRepository extends AppRepository implements PeopleRepositoryInterface
{
    public function __construct(?People $people = null)
    {
        parent::__construct($people ?? new People);
    }

    public function paginateIndex(?string $search = null, ?array $sort = null, int $limit = 10): LengthAwarePaginator
    {
        $query = $this->queryGet(function (Builder $query) use ($search) {
            if ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%');
            }
        });

        $allowedSortKeys = ['name', 'email', 'phone', 'gender', 'address', 'created_at', 'updated_at'];
        $sortKey = $sort['key'] ?? null;
        $sortOrder = strtolower($sort['order'] ?? 'asc');

        if (in_array($sortKey, $allowedSortKeys, true)) {
            $query->orderBy($sortKey, $sortOrder === 'desc' ? 'desc' : 'asc');
        }

        return $query->paginate($limit);
    }
}