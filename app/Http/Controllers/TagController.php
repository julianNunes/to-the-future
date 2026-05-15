<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Services\Interfaces\TagServiceInterface;
use Inertia\Inertia;

class TagController extends Controller
{
    public function __construct(private TagServiceInterface $tagService)
    {
    }

    /**
     * Returns data to Tag Management
     */
    public function index()
    {
        $data = $this->tagService->index();
        return Inertia::render('Tag/Index', $data);
    }

    /**
     * Create a new Tag
     */
    public function store(StoreTagRequest $request)
    {
        $this->tagService->create(
            $request->validated()['name'],
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a Tag
     * @param integer $id
     * @return void
     */
    public function update(UpdateTagRequest $request, int $id)
    {
        $this->tagService->update(
            $id,
            $request->validated()['name'],
        );
        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Deleta a Tag
     * @param integer $id
     */
    public function delete(int $id)
    {
        $this->tagService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }

    /**
     * Search by Tag name. Used in the "v-auto-complete" component
     * @param string $name
     * @return void
     */
    public function search(string $name)
    {
        $data = $this->tagService->search($name);
        return response()->json($data);
    }
}
