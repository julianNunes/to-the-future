<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\TagServiceInterface;
use Illuminate\Http\Request;
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
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required'],
        ]);

        $this->tagService->create(
            $request->name,
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a Tag
     * @param Request $request
     * @param integer $id
     * @return void
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'name' => ['required'],
        ]);

        $this->tagService->update(
            $id,
            $request->name,
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
