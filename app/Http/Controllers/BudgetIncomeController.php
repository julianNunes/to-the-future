<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\BudgetIncomeService;
use App\Services\Interfaces\BudgetIncomeServiceInterface;
use Illuminate\Http\Request;

class BudgetIncomeController extends Controller
{

    public function __construct(private BudgetIncomeServiceInterface $budgetIncomeSevice)
    {
    }

    /**
     * Create a new Income to Budget
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'description' => ['required'],
            'date' => ['required'],
            'value' => ['required'],
            'budget_id' => ['required'],
        ]);

        $this->budgetIncomeSevice->create(
            intval($request->budget_id),
            $request->description,
            $request->date,
            floatval($request->value),
            $request->remarks,
            collect($request->tags)
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a new Income to Budget
     * @param Request $request
     * @param int $id
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'description' => ['required'],
            'date' => ['required'],
            'value' => ['required'],
            // 'budget_id' => ['budget_id'],
        ]);

        $this->budgetIncomeSevice->update(
            $id,
            $request->description,
            $request->date,
            floatval($request->value),
            $request->remarks,
            collect($request->tags)
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
