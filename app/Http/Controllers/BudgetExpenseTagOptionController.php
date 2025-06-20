<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\BudgetExpenseTagOptionServiceInterface;
use Illuminate\Http\Request;

class BudgetExpenseTagOptionController extends Controller
{
    public function __construct(private BudgetExpenseTagOptionServiceInterface $budgetExpenseTagOptionService) {}

    /**
     * Create a new Expense to Budget
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'tags' => ['required'],
            'count_share' => ['required'],
            'budget_id' => ['required'],
        ]);

        $this->budgetExpenseTagOptionService->create(
            intval($request->budget_id),
            collect($request->tags),
            $request->count_share == 1 ? true : false,
            $request->group
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
            'tags' => ['required'],
            'count_share' => ['required'],
            'budget_id' => ['required'],
        ]);

        $this->budgetExpenseTagOptionService->update(
            $id,
            collect($request->tags),
            $request->count_share == 1 ? true : false,
            $request->group
        );

        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Deleta a Expense to Budget
     * @param int $id
     */
    public function delete(int $id)
    {
        $this->budgetExpenseTagOptionService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
