<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\FinancingInstallmentService;
use App\Services\FinancingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FinancingInstallmentController extends Controller
{
    protected $financingInstallmentService;

    public function __construct(FinancingInstallmentService $financingInstallmentService)
    {
        $this->financingInstallmentService = $financingInstallmentService;
    }

    /**
     * Retorna os dados para o index de Financiamento
     */
    public function index(int $id)
    {
        $data = $this->financingInstallmentService->index($id);
        return Inertia::render('Financing/Index', $data);
    }

    /**
     * Atualiza um Parcela do Financiamento
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'date' => ['required'],
            'value' => ['required'],
            // 'paid_value' => ['required'],
            'paid' => ['required'],
        ]);

        $this->financingInstallmentService->update(
            $id,
            $request->date,
            floatval($request->value),
            $request->paid_value ? floatval($request->paid_value) : null,
            $request->paid == 'true' ? true : false,
        );
        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Deleta uma Parcela do Financiamento
     */
    public function delete(int $id)
    {
        $this->financingInstallmentService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
