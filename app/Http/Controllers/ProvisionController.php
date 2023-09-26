<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ProvisionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProvisionController extends Controller
{

    protected $provisionService;

    public function __construct(ProvisionService $provisionService)
    {
        $this->provisionService = $provisionService;
    }

    /**
     * Retorna os dados para o index de Provisionamento
     */
    public function index()
    {
        $data = $this->provisionService->index();
        return Inertia::render('Provision/Index', $data);
    }


    /**
     * Cria um novo Provisionamento
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'description' => ['required'],
            'value' => ['required'],
            'week' => ['required'],
        ]);

        $this->provisionService->create(
            $request->description,
            floatval($request->value),
            $request->week,
            $request->remarks,
            $request->share_value,
            $request->share_user_id
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Atualiza um Provisionamento
     */
    public function update(Request $request, string $id)
    {
        $this->validate($request, [
            'description' => ['required'],
            'value' => ['required'],
            'week' => ['required'],
        ]);

        $provision = $this->provisionService->update(
            $id,
            $request->description,
            floatval($request->value),
            $request->week,
            $request->remarks,
            $request->share_value,
            $request->share_user_id
        );
        return $provision;
    }

    /**
     * Deleta um Provisionamento
     */
    public function destroy(string $id)
    {
        return $this->provisionService->delete($id);
    }
}
