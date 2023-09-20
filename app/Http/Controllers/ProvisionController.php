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
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->provisionService->all();

        return Inertia::render('Provision/Index', [
            'data' => $data,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'description' => ['required'],
            'value' => ['required'],
            'week' => ['nullable'],
        ]);

        $provision = $this->provisionService->create($request->all());
        return $provision;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->validate($request, [
            'description' => ['required'],
            'value' => ['required'],
            'week' => ['nullable'],
        ]);

        $provision = $this->provisionService->update($id, $request->all());
        return $provision;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->provisionService->delete($id);
    }
}
