<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\BudgetProvisionService;
use App\Services\Interfaces\BudgetProvisionServiceInterface;
use Illuminate\Http\Request;

class BudgetProvisionController extends Controller
{

    public function __construct(private BudgetProvisionServiceInterface $budgetProvisionSevice)
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

        $this->budgetProvisionSevice->create(
            $request->description,
            $request->date,
            floatval($request->value),
            $request->remarks,
            intval($request->budget_id),
            $request->share_value ? floatval($request->share_value) : null,
            $request->share_user_id,
            collect($request->tags)
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a new Provision to Budget
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

        $this->budgetProvisionSevice->update(
            $id,
            $request->description,
            $request->date,
            floatval($request->value),
            $request->remarks,
            intval($request->budget_id),
            $request->share_value ? floatval($request->share_value) : null,
            $request->share_user_id,
            collect($request->tags)
        );

        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Delete a new Provision to Budget
     * @param int $id
     */
    public function delete(int $id)
    {
        $this->budgetProvisionSevice->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
