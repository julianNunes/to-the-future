<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\CreditCardInvoiceServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class CreditCardInvoiceController extends Controller
{
    public function __construct(private CreditCardInvoiceServiceInterface $creditCardInvoiceService)
    {
    }

    /**
     * Returns data for Credit Card Invoice Management
     * @param integer $creditCardId
     * @return void
     */
    public function index(int $creditCardId)
    {
        $data = $this->creditCardInvoiceService->index($creditCardId);
        return Inertia::render('CreditCardInvoice/Index', $data);
    }

    /**
     * Create a new Invoice
     * @param Request $request
     * @return void
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'due_date' => ['required'],
            'closing_date' => ['required'],
            'year' => ['required'],
            'month' => ['required'],
            'credit_card_id' => ['required'],
        ]);

        $this->creditCardInvoiceService->createAutomatic(
            $request->due_date,
            $request->closing_date,
            $request->year,
            $request->month,
            $request->credit_card_id,
            $request->automatic_generate
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a Invoice
     * @param Request $request
     * @param integer $id
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'closed' => ['required'],
        ]);

        $this->creditCardInvoiceService->update(
            $id,
            $request->closed,
        );

        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Deleta a Invoice
     * @param integer $id
     */
    public function delete(int $id)
    {
        $this->creditCardInvoiceService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }

    /**
     * Mostrar os dados de uma fatura
     * @param integer $creditCardId
     * @param integer $id
     */
    public function show(int $id)
    {
        $data = $this->creditCardInvoiceService->show($id);
        return Inertia::render('CreditCardInvoice/Show', $data);
    }

    /**
     * Download xlxs file template
     * @return void
     */
    public function downloadTemplate()
    {
        return response()->download(public_path('storage/template/template-despesas.xlsx'), 'template-despesas.xlsx');
    }
}
