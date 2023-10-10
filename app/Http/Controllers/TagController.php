<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\TagService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TagController extends Controller
{

    protected $tagService;

    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;
    }

    /**
     * Retorna os dados para o index de Tag
     */
    public function index()
    {
        $data = $this->tagService->index();
        return Inertia::render('Tag/Index', $data);
    }

    /**
     * Cria um novo Tag
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
     * Atualiza uma Tag
     */
    public function update(Request $request, string $id)
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
     * Deleta uma Tag
     */
    public function destroy(string $id)
    {
        $this->tagService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }

    /**
     * Busca por nome de tags
     */
    public function search(Request $request, string $name)
    {
        $data = $this->tagService->search($name);
        return response()->json($data);
    }
}
