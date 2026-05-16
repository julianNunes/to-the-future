<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreditCardInvoice\StoreCreditCardInvoiceRequest;
use App\Http\Requests\CreditCardInvoice\UpdateCreditCardInvoiceRequest;
use App\Services\Interfaces\CreditCardInvoiceServiceInterface;
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
    public function store(StoreCreditCardInvoiceRequest $request)
    {
        $data = $request->validated();

        $this->creditCardInvoiceService->createAutomatic(
            $data['due_date'],
            $data['closing_date'],
            $data['year'],
            $data['month'],
            $data['credit_card_id'],
            $data['automatic_generate'] ?? false,
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a Invoice
     * @param Request $request
     * @param integer $id
     */
    public function update(UpdateCreditCardInvoiceRequest $request, int $id)
    {
        $data = $request->validated();

        $this->creditCardInvoiceService->update(
            $id,
            $data['closed'],
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
