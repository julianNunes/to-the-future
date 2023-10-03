<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\CreditCardInvoiceExpenseService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CreditCardInvoiceExpenseController extends Controller
{
    protected $creditCardInvoiceExpenseService;

    public function __construct(CreditCardInvoiceExpenseService $creditCardInvoiceExpenseService)
    {
        $this->creditCardInvoiceExpenseService = $creditCardInvoiceExpenseService;
    }

    /**
     * Retorna os dados para o index de Faturas de um Cartão de Credito
     */
    public function index(int $creditCardId)
    {
        $data = $this->creditCardInvoiceExpenseService->index($creditCardId);
        return Inertia::render('CreditCardInvoice/Index', $data);
    }


    /**
     * Cria uma nova Fatura
     */
    public function store(Request $request, int $creditCardId)
    {
        $this->validate($request, [
            'due_date' => ['required'],
            'closing_date' => ['required'],
            'year' => ['required'],
            'month' => ['required'],
        ]);

        $this->creditCardInvoiceExpenseService->create(
            $request->due_date,
            $request->closing_date,
            $request->year,
            $request->month,
            $creditCardId,
            $request->automatic_generate,
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }


    /**
     * Deleta um Provisionamento
     */
    public function destroy(string $id)
    {
        $this->creditCardInvoiceExpenseService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }

    public function show(int $creditCardId, int $id)
    {
        $data = $this->creditCardInvoiceExpenseService->show($creditCardId, $id);
        return Inertia::render('CreditCardInvoice/Show', $data);
    }
}
