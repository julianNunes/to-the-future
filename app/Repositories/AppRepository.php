<?php

namespace App\Repositories;

use App\Repositories\Interfaces\AppRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AppRepository implements AppRepositoryInterface
{
    /**
     * Eloquent model instance.
     */
    protected $model;

    /**
     * load default class dependencies.
     *
     * @param Model $model Illuminate\Database\Eloquent\Model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * return table name
     * @return string
     */
    public function tableName(): string
    {
        return $this->model->tableName();
    }

    /**
     * Builder Query
     * @param \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression $condition function | To array ['field' => 'value']
     * @param array $columns ['field1', 'field2', 'field3', ...]
     * @param array $simpleJoins [ 'tableName1' => ['field1', 'operator||field2', 'field2'], ...]
     * @param array $withs ['relationship1', 'relationship2', ...]
     * @return Builder query
     */
    public function queryGet($condition, array $columns = [], array $simpleJoins = [], array $withs = []): Builder
    {
        $query = $this->model;

        if (count($withs) > 0) {
            $query = $query->with($withs);
        }

        foreach ($simpleJoins as $table => $cols) {
            if (count($cols) == 2) {
                $query = $query->join($table, $cols[0], $cols[1]);
            }

            if (count($cols) == 3) {
                $query = $query->join($table, $cols[0], $cols[1], $cols[2]);
            }
        }

        if ($condition) {
            $query = $query->where($condition);
        }

        return count($columns) > 0 ? $query->select($columns) : $query->select();
    }

    /**
     * Builder Query and return Collection
     * @param \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression $condition function | To array ['field' => 'value']
     * @param array $columns ['field1', 'field2', 'field3', ...]
     * @param array $simpleJoins [ 'tableName1' => ['field1', 'operator||field2', 'field2'], ...]
     * @param array $withs ['relationship1', 'relationship2', ...]
     * @return Collection
     */
    public function get($condition, array $columns = [], array $simpleJoins = [], array $withs = []): Collection
    {
        $query = $this->queryGet($condition, $columns, $simpleJoins, $withs);
        return $query->get();
    }

    /**
     * Builder Query and return Collection
     * @param \Closure|string|array|\Illuminate\Contracts\Database\Query\Expression $condition function | To array ['field' => 'value']
     * @param array $columns ['field1', 'field2', 'field3', ...]
     * @param array $simpleJoins [ 'tableName1' => ['field1', 'operator||field2', 'field2'], ...]
     * @param array $withs ['relationship1', 'relationship2', ...]
     * @return Collection
     */
    public function getOne($condition, array $columns = [], array $simpleJoins = [], array $withs = []): Model|null
    {
        $query = $this->queryGet($condition, $columns, $simpleJoins, $withs);
        return $query->first();
    }

    /**
     * Find object for id
     * @param int $id
     * @return Model
     */
    public function show(int $id, array $withs = []): Model
    {
        return $this->queryGet(['id' => $id], [], [], $withs)->first();
    }

    /**
     * Create or Update a model with id or Model
     * @param array $data ['field' => 'value', 'field' => 'value', 'field' => 'value', ... ]
     * @param Model|null $model
     * @return Model
     */
    public function store(array $data, ?Model $model = null): Model
    {
        if (is_null($model)) {
            if (array_key_exists('id', $data)) {
                $model = $this->show($data['id']);
            } else {
                $model = new $this->model;
            }
        }

        $model->fill($data);
        $model->save();
        return $model;
    }

    /**
     * Delete object for id
     * @param $id
     * @return bool
     */
    public function delete($id): bool
    {
        return $this->model->where('id', $id)->delete($id);
    }
}
