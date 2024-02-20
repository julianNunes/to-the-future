<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\BudgetIncomeService;
use Illuminate\Http\Request;

class BudgetIncomeController extends Controller
{

    public function __construct(private BudgetIncomeService $budgetIncomeSevice)
    {
    }

    /**
     * Cria uma nova Receita para um Orçamento
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
            $request->description,
            $request->date,
            floatval($request->value),
            $request->remarks,
            intval($request->budget_id),
            collect($request->tags)
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Atualiza uma nova Receita para um Orçamento
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
     * Deleta uma nova Receita para um Orçamento
     */
    public function delete(int $id)
    {
        $this->budgetIncomeSevice->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
