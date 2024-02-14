<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\BudgetExpenseService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BudgetExpenseController extends Controller
{
    protected $budgetExpenseSevice;

    public function __construct(BudgetExpenseService $budgetExpenseSevice)
    {
        $this->budgetExpenseSevice = $budgetExpenseSevice;
    }

    /**
     * Cria uma nova Despesa para um Orçamento
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'description' => ['required'],
            'date' => ['required'],
            'value' => ['required'],
            'paid' => ['required'],
            'budget_id' => ['required'],
        ]);

        $this->budgetExpenseSevice->create(
            $request->description,
            $request->date,
            floatval($request->value),
            $request->remarks,
            $request->paid == 1 ? true : false,
            intval($request->budget_id),
            $request->share_value ? floatval($request->share_value) : null,
            $request->share_user_id,
            $request->financing_installment_id ? intval($request->financing_installment_id) : null,
            collect($request->tags)
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Atualiza uma nova Despesa para um Orçamento
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'description' => ['required'],
            'date' => ['required'],
            'value' => ['required'],
            'paid' => ['required'],
            // 'budget_id' => ['budget_id'],
        ]);

        $this->budgetExpenseSevice->update(
            $id,
            $request->description,
            $request->date,
            floatval($request->value),
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
     * Deleta uma nova Despesa para um Orçamento
     */
    public function delete(int $id)
    {
        $this->budgetExpenseSevice->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
