<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\BudgetExpenseService;
use App\Services\Interfaces\BudgetExpenseServiceInterface;
use App\Services\Interfaces\BudgetGoalServiceInterface;
use Illuminate\Http\Request;

class BudgetGoalController extends Controller
{
    public function __construct(private BudgetGoalServiceInterface $budgetGoalService)
    {
    }

    /**
     * Create a new Expense to Budget
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'description' => ['required'],
            'value' => ['required'],
            'group' => ['required'],
            'count_only_share' => ['required'],
            'budget_id' => ['required'],
        ]);

        $this->budgetGoalService->create(
            $request->description,
            floatval($request->value),
            $request->group,
            $request->count_only_share == 1 ? true : false,
            intval($request->budget_id),
            collect($request->tags)
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a new Expense to Budget
     * @param Request $request
     * @param int $id
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'description' => ['required'],
            'value' => ['required'],
            'group' => ['required'],
            'count_only_share' => ['required'],
            'budget_id' => ['required'],
        ]);

        $this->budgetGoalService->update(
            $id,
            $request->description,
            floatval($request->value),
            $request->group,
            $request->count_only_share == 1 ? true : false,
            collect($request->tags)
        );

        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Deleta a Expense to Budget
     * @param int $id
     */
    public function delete(int $id)
    {
        $this->budgetGoalService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
