<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\ProvisionServiceInterface;
use App\Services\ProvisionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProvisionController extends Controller
{
    public function __construct(private ProvisionServiceInterface $provisionService)
    {
    }

    /**
     * Returns data to Provision Management
     */
    public function index()
    {
        $data = $this->provisionService->index();
        return Inertia::render('Provision/Index', $data);
    }

    /**
     * Create a new Provision
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'description' => ['required'],
            'value' => ['required'],
            'group' => ['required'],
        ]);

        $this->provisionService->create(
            $request->description,
            floatval($request->value),
            $request->group,
            $request->remarks,
            $request->share_value ? floatval($request->share_value) : null,
            $request->share_user_id,
            collect($request->tags)
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a Provision
     * @param Request $request
     * @param integer $id
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'description' => ['required'],
            'value' => ['required'],
            'group' => ['required'],
        ]);

        $this->provisionService->update(
            $id,
            $request->description,
            floatval($request->value),
            $request->group,
            $request->remarks,
            $request->share_value,
            $request->share_user_id,
            collect($request->tags)
        );
        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Delete a Provision
     * @param integer $id
     * @return void
     */
    public function delete(int $id)
    {
        $this->provisionService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
