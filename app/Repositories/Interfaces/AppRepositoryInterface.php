<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface AppRepositoryInterface
{
    /**
     * return table name
     * @return string
     */
    public function tableName(): string;

    /**
     * Builder Query
     * @param \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression $condition function | To array ['field' => 'value']
     * @param array $columns ['field1', 'field2', 'field3', ...]
     * @param array $simpleJoins [ 'tableName1' => ['field1', 'operator||field2', 'field2'], ...]
     * @param array $withs ['relationship1', 'relationship2', ...]
     * @return Builder query
     */
    public function queryGet($condition, array $columns = [], array $simpleJoins = [], array $withs = []): Builder;

    /**
     * Builder Query and return Collection
     * @param \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression $condition function | To array ['field' => 'value']
     * @param array $columns ['field1', 'field2', 'field3', ...]
     * @param array $simpleJoins [ 'tableName1' => ['field1', 'operator||field2', 'field2'], ...]
     * @param array $withs ['relationship1', 'relationship2', ...]
     * @return Collection
     */
    public function get(mixed $condition, array $columns = [], array $simpleJoins = [], array $withs = []): Collection;

    /**
     * Builder Query and return Model
     * @param \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression $condition function | To array ['field' => 'value']
     * @param array $columns ['field1', 'field2', 'field3', ...]
     * @param array $simpleJoins [ 'tableName1' => ['field1', 'operator||field2', 'field2'], ...]
     * @param array $withs ['relationship1', 'relationship2', ...]
     * @return Model
     */
    public function getOne(mixed $condition, array $columns = [], array $simpleJoins = [], array $withs = []): Model|null;

    /**
     * Find object for id
     * @param int $id
     * @return Model
     */
    public function show(int $id, array $withs = []): Model;

    /**
     * Create or Update a model with id or Model
     * @param array $data ['field' => 'value', 'field' => 'value', 'field' => 'value', ... ]
     * @param Model|null $model
     * @return Model
     */
    public function store(array $data, ?Model $model = null): Model;

    /**
     * Delete object for id
     * @param $id
     * @return bool
     */
    public function delete($id): bool;
}
