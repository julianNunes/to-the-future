<?php

namespace App\Services\Interfaces;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface TagServiceInterface
{
    /**
     * Retorna os dados para o index de Tag
     * @return Array
     */
    public function index(): array;

    /**
     * Cria um novo Tag
     * @param string $name
     * @return Tag
     */
    public function create(string $name): Tag;

    /**
     * Atualiza uma Tag
     * @param int $id
     * @param string $name
     * @return Tag
     */
    public function update(int $id, string $name): Tag;

    /**
     * Deleta uma Tag
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Busca tags atraves do nome
     * @param string $name
     * @return Collection
     */
    public function search(string $name): Collection;

    /**
     * Metodo generico responsavel por salvar/atualizar as tagas para um determinado Model
     * @param Model $model
     * @param Collection|null $tags
     * @return void
     */
    public function saveTagsToModel(Model $model, Collection $tags = null);
}
