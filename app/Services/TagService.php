<?php

namespace App\Services;

use App\Models\Tag;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class TagService
{
    public function __construct()
    {
    }

    /**
     * Retorna os dados para o index de Tag
     * @return Array
     */
    public function index(): array
    {
        $tags = Tag::where('user_id', auth()->user()->id)->orderBy('name')->get();

        return [
            'tags' => $tags,
        ];
    }

    /**
     * Cria um novo Tag
     * @param string $name
     * @return Tag
     */
    public function create(
        string $name
    ): Tag {
        $tag = Tag::where('name', $name)->first();

        if ($tag) {
            throw new Exception('tag.already-exists');
        }

        $tag = new Tag([
            'name' => $name,
            'user_id' => auth()->user()->id
        ]);

        $tag->save();
        return $tag;
    }

    /**
     * Atualiza uma Tag
     * @param int $id
     * @param string $data
     * @return bool
     */
    public function update(
        int $id,
        string $name
    ): bool {
        $tag = Tag::where('name', $name)->first();

        if ($tag) {
            throw new Exception('tag.already-exists');
        }

        $tag = Tag::find($id);

        if (!$tag) {
            throw new Exception('tag.not-found');
        }

        return $tag->update([
            'name' => $name,
            'user_id' => auth()->user()->id
        ]);
    }

    /**
     * Deleta uma Tag
     * @param bool
     */
    public function delete(int $id): bool
    {
        $tag = Tag::find($id);

        if (!$tag) {
            throw new Exception('tag.not-found');
        }

        return $tag->delete();
    }


    public function search(string $name): Collection
    {
        $name = strtoupper($name);
        return Tag::select('name')
            ->where('name', 'LIKE', "%{$name}%")
            ->where('user_id', auth()->user()->id)
            ->get();
    }

    /**
     * @todo DOCUMENTAR
     * @param Collection $tags
     * @param Model $model
     * @return void
     */
    public static function saveTagsToModel(Collection $tags, Model $model)
    {
        if ($tags && $tags->count()) {
            $tags_sync = collect();

            // Busca as Tags no banco
            foreach ($tags as $tag) {
                $new_tag = Tag::where('name', $tag['name'])->first();

                if (!$new_tag) {
                    $new_tag = new Tag([
                        'name' => $tag['name'],
                        'user_id' => auth()->user()->id
                    ]);
                    $new_tag->save();
                }

                $tags_sync->push($new_tag);
            }

            if ($tags_sync->count()) {
                $model->tags()->sync($tags_sync->pluck('id')->toArray());
            } else {
                $model->tags()->detach();
            }
        } else {
            $model->tags()->detach();
        }
    }
}
