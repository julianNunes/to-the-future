<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\BudgetService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BudgetController extends Controller
{

    protected $budgetService;

    public function __construct(BudgetService $budgetService)
    {
        $this->budgetService = $budgetService;
    }

    /**
     * Retorna os dados para o index de Cartão de Credito
     */
    public function index(Request $request, string $year)
    {
        $data = $this->budgetService->index($year);
        return Inertia::render('Budget/Index', $data);
    }


    /**
     * Cria um novo Cartão de Credito
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required'],
            'digits' => ['required'],
            'due_date' => ['required'],
            'closing_date' => ['required'],
            'is_active' => ['required'],
        ]);

        $this->budgetService->create(
            $request->name,
            $request->digits,
            $request->due_date,
            $request->closing_date,
            $request->is_active == '1'
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Atualiza um Cartão de Credito
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'name' => ['required'],
            'digits' => ['required'],
            'due_date' => ['required'],
            'closing_date' => ['required'],
            'is_active' => ['required'],
        ]);

        $this->budgetService->update(
            $id,
            $request->name,
            $request->digits,
            $request->due_date,
            $request->closing_date,
            $request->is_active == '1'
        );
        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Deleta um Cartão de Credito
     */
    public function delete(int $id)
    {
        $this->budgetService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
