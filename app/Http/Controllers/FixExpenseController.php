<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\FixExpenseService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FixExpenseController extends Controller
{

    protected $fixExpenseService;

    public function __construct(FixExpenseService $fixExpenseService)
    {
        $this->fixExpenseService = $fixExpenseService;
    }

    /**
     * Retorna os dados para o index de Despesa FIxa
     */
    public function index()
    {
        $data = $this->fixExpenseService->index();
        return Inertia::render('FixExpense/Index', $data);
    }


    /**
     * Cria um novo Despesa FIxa
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'description' => ['required'],
            'due_date' => ['required'],
            'value' => ['required'],
        ]);

        $this->fixExpenseService->create(
            $request->description,
            $request->due_date,
            floatval($request->value),
            $request->remarks,
            $request->share_value ? floatval($request->share_value) : null,
            $request->share_user_id,
            collect($request->tags)
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Atualiza um Despesa FIxa
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'description' => ['required'],
            'due_date' => ['required'],
            'value' => ['required'],
        ]);

        $this->fixExpenseService->update(
            $id,
            $request->description,
            $request->due_date,
            floatval($request->value),
            $request->remarks,
            $request->share_value,
            $request->share_user_id,
            collect($request->tags)
        );
        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Deleta um Despesa FIxa
     */
    public function delete(int $id)
    {
        $this->fixExpenseService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
