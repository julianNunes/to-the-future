<?php

namespace App\Services;

use App\Models\Tag;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Services\Interfaces\TagServiceInterface;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class TagService implements TagServiceInterface
{
    public function __construct(private TagRepositoryInterface $tagRepository)
    {
    }

    /**
     * Retorna os dados para o index de Tag
     * @return Array
     */
    public function index(): array
    {
        $tags = $this->tagRepository->index();
        return [
            'tags' => $tags,
        ];
    }

    /**
     * Cria um novo Tag
     * @param string $name
     * @return Tag
     */
    public function create(string $name): Tag
    {
        $tag = $this->tagRepository->getOne(['name' => $name]);

        if ($tag) {
            throw new Exception('tag.already-exists');
        }

        $tag = $this->tagRepository->store([
            'name' => $name,
            'user_id' => auth()->user()->id,
        ]);

        return $tag;
    }

    /**
     * Atualiza uma Tag
     * @param int $id
     * @param string $name
     * @return Tag
     */
    public function update(int $id, string $name): Tag
    {
        $tag = $this->tagRepository->getOne(function (Builder $query) use ($id, $name) {
            $query->where('name', $name)->where('id', '!=', $id);
        });

        if ($tag) {
            throw new Exception('tag.already-exists');
        }

        $tag = $this->tagRepository->show($id);

        if (!$tag) {
            throw new Exception('tag.not-found');
        }

        return $this->tagRepository->store([
            'name'    => $name,
            'user_id' => auth()->user()->id,
        ], $tag);
    }

    /**
     * Deleta uma Tag
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $tag = $this->tagRepository->show($id);

        if (!$tag) {
            throw new Exception('tag.not-found');
        }

        return $this->tagRepository->delete($id);
    }

    /**
     * Busca tags atraves do nome
     * @param string $name
     * @return Collection
     */
    public function search(string $name): Collection
    {
        return $this->tagRepository->search($name);
    }

    /**
     * Metodo generico responsavel por salvar/atualizar as tagas para um determinado Model
     * @param Model $model
     * @param Collection|null $tags
     * @return void
     */
    public function saveTagsToModel(Model $model, Collection $tags = null)
    {
        $this->tagRepository->saveTagsToModel($model, $tags);
    }
}
