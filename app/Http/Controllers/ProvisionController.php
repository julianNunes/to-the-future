<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provision\StoreProvisionRequest;
use App\Http\Requests\Provision\UpdateProvisionRequest;
use App\Services\Interfaces\ProvisionServiceInterface;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProvisionController extends Controller
{
    public function __construct(private ProvisionServiceInterface $provisionService)
    {
    }

    /**
     * Returns data to Provision Management
     */
    public function index(): Response
    {
        $data = $this->provisionService->index();
        return Inertia::render('Provision/Index', $data);
    }

    /**
     * Create a new Provision
     */
    public function store(StoreProvisionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->provisionService->create(
            $data['description'],
            $data['value'],
            $data['group'],
            $data['remarks'] ?? null,
            $data['share_value'] ?? null,
            $data['share_user_id'] ?? null,
            collect($data['tags'] ?? [])
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a Provision
     * @param integer $id
     */
    public function update(UpdateProvisionRequest $request, int $id): RedirectResponse
    {
        $data = $request->validated();

        $this->provisionService->update(
            $id,
            $data['description'],
            $data['value'],
            $data['group'],
            $data['remarks'] ?? null,
            $data['share_value'] ?? null,
            $data['share_user_id'] ?? null,
            collect($data['tags'] ?? [])
        );
        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Delete a Provision
     * @param integer $id
     * @return void
     */
    public function delete(int $id): RedirectResponse
    {
        $this->provisionService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
