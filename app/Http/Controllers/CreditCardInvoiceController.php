<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\CreditCardInvoiceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CreditCardInvoiceController extends Controller
{
    protected $creditCardInvoiceService;

    public function __construct(CreditCardInvoiceService $creditCardInvoiceService)
    {
        $this->creditCardInvoiceService = $creditCardInvoiceService;
    }

    /**
     * Retorna os dados para o index de Faturas de um Cartão de Credito
     * @param integer $creditCardId
     * @return void
     */
    public function index(int $creditCardId)
    {
        $data = $this->creditCardInvoiceService->index($creditCardId);
        return Inertia::render('CreditCardInvoice/Index', $data);
    }


    /**
     * Cria uma nova Fatura
     * @param Request $request
     * @param integer $creditCardId
     * @return void
     */
    public function store(Request $request, int $creditCardId)
    {
        $this->validate($request, [
            'due_date' => ['required'],
            'closing_date' => ['required'],
            'year' => ['required'],
            'month' => ['required'],
        ]);

        $this->creditCardInvoiceService->create(
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
     * @param string $id
     * @return void
     */
    public function destroy(string $id)
    {
        $this->creditCardInvoiceService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }

    /**
     *
     * @param integer $creditCardId
     * @param integer $id
     * @return void
     */
    public function show(int $creditCardId, int $id)
    {
        $data = $this->creditCardInvoiceService->show($creditCardId, $id);
        return Inertia::render('CreditCardInvoice/Show', $data);
    }
}
