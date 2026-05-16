<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetIncome\StoreBudgetIncomeRequest;
use App\Http\Requests\BudgetIncome\UpdateBudgetIncomeRequest;
use App\Services\Interfaces\BudgetIncomeServiceInterface;

class BudgetIncomeController extends Controller
{

    public function __construct(private BudgetIncomeServiceInterface $budgetIncomeSevice)
    {
    }

    /**
     * Create a new Income to Budget
     * @param Request $request
     */
    public function store(StoreBudgetIncomeRequest $request)
    {
        $data = $request->validated();

        $this->budgetIncomeSevice->create(
            $data['budget_id'],
            $data['description'],
            $data['date'],
            $data['value'],
            $data['remarks'] ?? null,
            collect($data['tags'] ?? [])
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a new Income to Budget
     * @param Request $request
     * @param int $id
     */
    public function update(UpdateBudgetIncomeRequest $request, int $id)
    {
        $data = $request->validated();

        $this->budgetIncomeSevice->update(
            $id,
            $data['description'],
            $data['date'],
            $data['value'],
            $data['remarks'] ?? null,
            collect($data['tags'] ?? [])
        );

        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Delete a new Income to Budget
     * @param int $id
     */
    public function delete(int $id)
    {
        $this->budgetIncomeSevice->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
