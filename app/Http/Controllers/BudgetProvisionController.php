<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetProvision\StoreBudgetProvisionRequest;
use App\Http\Requests\BudgetProvision\UpdateBudgetProvisionRequest;
use App\Services\Interfaces\BudgetProvisionServiceInterface;

class BudgetProvisionController extends Controller
{

    public function __construct(private BudgetProvisionServiceInterface $budgetProvisionService) {}

    /**
     * Create a new Provision to Budget
     * @param Request $request
     */
    public function store(StoreBudgetProvisionRequest $request)
    {
        $data = $request->validated();

        $this->budgetProvisionService->create(
            $data['budget_id'],
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
     * Update a Provision to Budget
     * @param Request $request
     * @param int $id
     */
    public function update(UpdateBudgetProvisionRequest $request, int $id)
    {
        $data = $request->validated();

        $this->budgetProvisionService->update(
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
     * Delete a Provision to Budget
     * @param int $id
     */
    public function delete(int $id)
    {
        $this->budgetProvisionService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }

    /**
     * Search by description. Used in the "v-auto-complete" component
     * @param string $description
     * @return void
     */
    public function search(string $description)
    {
        $data = $this->budgetProvisionService->search($description);
        return response()->json($data);
    }
}
