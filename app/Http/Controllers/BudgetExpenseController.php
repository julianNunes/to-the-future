<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\BudgetExpenseServiceInterface;
use Illuminate\Http\Request;

class BudgetExpenseController extends Controller
{
    public function __construct(private BudgetExpenseServiceInterface $budgetExpenseSevice) {}

    /**
     * Create a new Expense to Budget
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

        if ($request->portion_total && intval($request->portion_total) >= 2) {
            $this->budgetExpenseSevice->createWithPortions(
                intval($request->budget_id),
                $request->description,
                $request->date,
                floatval($request->value),
                $request->portion ? intval($request->portion) : null,
                $request->portion_total ? intval($request->portion_total) : null,
                $request->date,
                $request->remarks,
                $request->paid == 1 ? true : false,
                $request->share_value ? floatval($request->share_value) : null,
                $request->share_user_id,
                $request->financing_installment_id ? intval($request->financing_installment_id) : null,
                collect($request->tags)
            );
        } else {
            $this->budgetExpenseSevice->create(
                intval($request->budget_id),
                $request->description,
                $request->date,
                floatval($request->value),
                $request->portion ? intval($request->portion) : null,
                $request->portion_total ? intval($request->portion_total) : null,
                $request->date,
                $request->remarks,
                $request->paid == 1 ? true : false,
                $request->share_value ? floatval($request->share_value) : null,
                $request->share_user_id,
                $request->financing_installment_id ? intval($request->financing_installment_id) : null,
                collect($request->tags)
            );
        }

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a Expense to Budget
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
     */
    public function delete(int $id)
    {
        $this->budgetExpenseSevice->delete($id);

        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
