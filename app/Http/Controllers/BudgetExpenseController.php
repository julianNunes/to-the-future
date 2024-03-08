<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\BudgetExpenseServiceInterface;
use Illuminate\Http\Request;

class BudgetExpenseController extends Controller
{
    public function __construct(private BudgetExpenseServiceInterface $budgetExpenseSevice)
    {
    }

    /**
     * Create a new Expense to Budget
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'budget_id' => ['required'],
            'description' => ['required'],
            'date' => ['required'],
            'value' => ['required'],
            'group' => ['required'],
            'paid' => ['required'],
        ]);

        $this->budgetExpenseSevice->create(
            intval($request->budget_id),
            $request->description,
            $request->date,
            floatval($request->value),
            $request->date,
            $request->remarks,
            $request->paid == 1 ? true : false,
            $request->share_value ? floatval($request->share_value) : null,
            $request->share_user_id,
            $request->financing_installment_id ? intval($request->financing_installment_id) : null,
            collect($request->tags)
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a Expense to Budget
     * @param Request $request
     * @param int $id
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'description' => ['required'],
            'date' => ['required'],
            'value' => ['required'],
            'group' => ['required'],
            // 'paid' => ['required'],
            // 'budget_id' => ['budget_id'],
        ]);

        $this->budgetExpenseSevice->update(
            $id,
            $request->description,
            $request->date,
            floatval($request->value),
            $request->group,
            $request->remarks,
            $request->paid == 1 ? true : false,
            $request->share_value ? floatval($request->share_value) : null,
            $request->share_user_id,
            $request->financing_installment_id ? intval($request->financing_installment_id) : null,
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
        $this->budgetExpenseSevice->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
