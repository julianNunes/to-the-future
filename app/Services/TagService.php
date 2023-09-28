<?php

namespace App\Services;

use App\Models\Tag;
use Exception;

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
        $tags = Tag::where('user_id', auth()->user()->id)->get();

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

    // /**
    //  * Armazenamento da Imagem do Tagamento
    //  * @param object $image
    //  * @return string
    //  */
    // public function storeImageTag(object $image)
    // {
    //     return $image->store("/tags");
    // }
}