<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\BudgetService;
use App\Services\Interfaces\BudgetServiceInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BudgetController extends Controller
{
    public function __construct(private BudgetServiceInterface $budgetService)
    {
    }

    /**
     * Retorna os dados para o index de Orçamento
     */
    public function index(Request $request, string $year)
    {
        $data = $this->budgetService->index($year);
        return Inertia::render('Budget/Index', $data);
    }

    /**
     * Cria um novo Orçamento
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'year' => ['required'],
            'month' => ['required'],
        ]);

        $this->budgetService->createComplete(
            auth()->user()->id,
            $request->year,
            $request->month,
            $request->automaticGenerateYear,
            $request->includeFixExpense,
            $request->includeProvision
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Clona um Orçamento
     */
    public function clone(Request $request, int $id)
    {
        $this->validate($request, [
            'id' => ['required'],
            'year' => ['required'],
            'month' => ['required'],
        ]);

        $this->budgetService->clone(
            $id,
            $request->year,
            $request->month,
            $request->includeProvision,
            $request->cloneBugdetExpense,
            $request->cloneBugdetIncome,
            $request->cloneBugdetGoals
        );

        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Atualiza um Orçamento
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'closed' => ['closed'],
        ]);

        $this->budgetService->update(
            $id,
            $request->closed
        );
        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Deleta um Orçamento
     */
    public function delete(int $id)
    {
        $this->budgetService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }

    /**
     * Mostra um Orçamento
     */
    public function findByYearMonth(string $year, string $month)
    {
        $budget = $this->budgetService->findByYearMonth(
            $year,
            $month
        );

        return to_route('budget.show', ['id' => $budget->id]);
    }

    /**
     * Mostra um Orçamento com seus detalhamentos
     */
    public function show(int $id)
    {
        $data = $this->budgetService->show(
            $id
        );
        return Inertia::render('Budget/Show', $data);
    }
}
