<?php

namespace App\Repositories;

use App\Models\Tag;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class TagRepository extends AppRepository implements TagRepositoryInterface
{
    public function __construct(?Tag $tag = null)
    {
        parent::__construct($tag ?? new Tag);
    }

    /**
     * Retorna os dados para o index de Tag
     * @return Array
     */
    public function index(): Collection
    {
        return $this->model->where('user_id', auth()->user()->id)->orWhereNull('user_id')->orderBy('user_id', 'ASC')->orderBy('name', 'ASC')->get();
    }

    /**
     * Busca tags atraves do nome
     * @param string $name
     * @return Collection
     */
    public function search(string $name): Collection
    {
        $name = strtoupper($name);
        return $this->model->select('name')
            ->where('name', 'LIKE', "%{$name}%")
            ->where(function (Builder $query) {
                $query->where('user_id', auth()->user()->id)->orWhereNull('user_id');
            })
            ->get();
    }

    /**
     * Metodo generico responsavel por salvar/atualizar as tagas para um determinado Model
     * @param Model $model
     * @param Collection|null $tags
     * @return void
     */
    public function saveTagsToModel(Model $model, ?Collection $tags = null): void
    {
        if ($tags && $tags->count()) {
            $tagNames = $tags->pluck('name')->filter()->unique()->values();

            if (!$tagNames->count()) {
                $model->tags()->detach();
                return;
            }

            $existingTags = $this->model
                ->newQuery()
                ->whereIn('name', $tagNames->all())
                ->get()
                ->keyBy('name');

            $tagsSync = collect();

            foreach ($tagNames as $tagName) {
                $newTag = $existingTags->get($tagName);

                if (!$newTag) {
                    $newTag = $this->store([
                        'name' => $tagName,
                        'user_id' => auth()->user()->id
                    ]);
                }

                $tagsSync->push($newTag);
            }

            if ($tagsSync->count()) {
                $model->tags()->sync($tagsSync->pluck('id')->toArray());
            } else {
                $model->tags()->detach();
            }
        } else {
            $model->tags()->detach();
        }
    }
}
