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
    public function index(int $financingId)
    {
        $data = $this->financingInstallmentService->index($financingId);
        return Inertia::render('Financing/Show', $data);
    }

    /**
     * Atualiza um Parcela do Financiamento
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'date' => ['required'],
            'value' => ['required'],
            'paid' => ['required'],
            // 'paid_value' => ['required'],
        ]);

        $this->financingInstallmentService->update(
            $id,
            $request->date,
            floatval($request->value),
            $request->paid == 1 ? true : false,
            $request->payment_date,
            $request->paid_value ? floatval($request->paid_value) : null,
        );
        return redirect()->back()->with('success', 'default.sucess-update');
    }
}