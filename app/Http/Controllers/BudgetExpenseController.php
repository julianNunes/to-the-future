<?php

namespace App\Http\Controllers;

use App\Http\Requests\BudgetExpense\StoreBudgetExpenseRequest;
use App\Http\Requests\BudgetExpense\UpdateBudgetExpenseRequest;
use App\Services\Interfaces\BudgetExpenseServiceInterface;

class BudgetExpenseController extends Controller
{
    public function __construct(private BudgetExpenseServiceInterface $budgetExpenseSevice) {}

    /**
     * Create a new Expense to Budget
     */
    public function store(StoreBudgetExpenseRequest $request)
    {
        $data = $request->validated();

        if (($data['portion_total'] ?? null) && $data['portion_total'] >= 2) {
            $this->budgetExpenseSevice->createWithPortions(
                $data['budget_id'],
                $data['description'],
                $data['date'],
                $data['value'],
                $data['portion'] ?? null,
                $data['portion_total'] ?? null,
                $data['group'],
                $data['remarks'] ?? null,
                $data['paid'],
                $data['share_value'] ?? null,
                $data['share_user_id'] ?? null,
                collect($data['tags'] ?? [])
            );
        } else {
            $this->budgetExpenseSevice->create(
                $data['budget_id'],
                $data['description'],
                $data['date'],
                $data['value'],
                $data['group'],
                $data['remarks'] ?? null,
                $data['paid'],
                $data['share_value'] ?? null,
                $data['share_user_id'] ?? null,
                collect($data['tags'] ?? [])
            );
        }

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a Expense to Budget
     */
    public function update(UpdateBudgetExpenseRequest $request, int $id)
    {
        $data = $request->validated();

        $this->budgetExpenseSevice->update(
            $id,
            $data['description'],
            $data['date'],
            $data['value'],
            $data['group'],
            $data['remarks'] ?? null,
            $data['paid'] ?? false,
            $data['share_value'] ?? null,
            $data['share_user_id'] ?? null,
            collect($data['tags'] ?? [])
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

    /**
     * Delete all Expenses with Portion from a Budget
     *
     * @param string $groupPortion
     * @return void
     */
    public function deleteAllPortions(string $groupPortion)
    {
        $this->budgetExpenseSevice->deleteAllPortions($groupPortion);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
