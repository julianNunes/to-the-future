<?php

namespace App\Services\Interfaces;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface TagServiceInterface
{
    /**
     * Returns data to Tag Management
     * @return Array
     */
    public function index(): array;

    /**
     * Create a new Tag
     * @param string $name
     * @return Tag
     */
    public function create(string $name): Tag;

    /**
     * Update a Tag
     * @param int $id
     * @param string $name
     * @return Tag
     */
    public function update(int $id, string $name): Tag;

    /**
     * Deleta a Tag
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Search by Tag name. Used in the "v-auto-complete" component
     * @param string $name
     * @return Collection
     */
    public function search(string $name): Collection;

    /**
     * Generic method responsible for saving/updating rates for a given Model
     * @param Model $model
     * @param Collection|null $tags
     * @return void
     */
    public function saveTagsToModel(Model $model, Collection $tags = null);
}
