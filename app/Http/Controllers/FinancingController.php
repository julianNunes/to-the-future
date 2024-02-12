<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\FinancingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FinancingController extends Controller
{
    protected $financingService;

    public function __construct(FinancingService $financingService)
    {
        $this->financingService = $financingService;
    }

    /**
     * Retorna os dados para o index de Financiamento
     */
    public function index()
    {
        $data = $this->financingService->index();
        return Inertia::render('Financing/Index', $data);
    }

    /**
     * Cria um novo Financiamento
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'description' => ['required'],
            'start_date' => ['required'],
            'total' => ['required'],
            'portion_total' => ['required'],
            // 'remarks' => ['required'],
            'start_date_installment' => ['required'],
            'value_installment' => ['required'],
        ]);

        $this->financingService->create(
            $request->description,
            $request->start_date,
            floatval($request->total),
            $request->fees_monthly ? floatval($request->fees_monthly) : null,
            intval($request->portion_total),
            $request->start_date_installment,
            floatval($request->value_installment),
            $request->remarks
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Atualiza um Financiamento
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'description' => ['required'],
            'start_date' => ['required'],
            'total' => ['required'],
            // 'remarks' => ['required'],
        ]);

        $this->financingService->update(
            $id,
            $request->description,
            $request->start_date,
            floatval($request->total),
            $request->fees_monthly ? floatval($request->fees_monthly) : null,
            $request->value_installment ? floatval($request->value_installment) : null,
            $request->remarks
        );

        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Deleta um Financiamento
     */
    public function delete(int $id)
    {
        $this->financingService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
