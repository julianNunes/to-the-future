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
     * Returns data to Tag Management
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
     * Create a new Tag
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
     * Update a Tag
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
     * Deleta a Tag
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
     * Search by Tag name. Used in the "v-auto-complete" component
     * @param string $name
     * @return Collection
     */
    public function search(string $name): Collection
    {
        return $this->tagRepository->search($name);
    }

    /**
     * Generic method responsible for saving/updating rates for a given Model
     * @param Model $model
     * @param Collection|null $tags
     * @return void
     */
    public function saveTagsToModel(Model $model, Collection $tags = null)
    {
        $this->tagRepository->saveTagsToModel($model, $tags);
    }
}
