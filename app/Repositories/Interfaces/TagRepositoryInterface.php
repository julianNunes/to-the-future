<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface TagRepositoryInterface extends AppRepositoryInterface
{
    /**
     * Retorna os dados para o index de Tag
     * @return Array
     */
    public function index(): Collection;

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
