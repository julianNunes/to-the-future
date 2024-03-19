<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\BudgetProvisionService;
use App\Services\Interfaces\BudgetProvisionServiceInterface;
use Illuminate\Http\Request;

class BudgetProvisionController extends Controller
{

    public function __construct(private BudgetProvisionServiceInterface $budgetProvisionService)
    {
    }

    /**
     * Create a new Provision to Budget
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'description' => ['required'],
            'value' => ['required'],
            'group' => ['required'],
            'budget_id' => ['required'],
        ]);

        $this->budgetProvisionService->create(
            intval($request->budget_id),
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
     * Update a Provision to Budget
     * @param Request $request
     * @param int $id
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'description' => ['required'],
            'value' => ['required'],
            'group' => ['required'],
        ]);

        $this->budgetProvisionService->update(
            $id,
            $request->description,
            floatval($request->value),
            $request->group,
            $request->remarks,
            intval($request->budget_id),
            $request->share_value ? floatval($request->share_value) : null,
            $request->share_user_id,
            collect($request->tags)
        );

        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Delete a Provision to Budget
     * @param int $id
     */
    public function delete(int $id)
    {
        $this->budgetProvisionService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
