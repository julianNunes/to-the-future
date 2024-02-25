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
     * Deleta uma Fatura
     * @param integer $id
     * @return void
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
     * @return void
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
        Log::info('downloadtemplate');
        return response()->download(public_path('storage/template/template-despesas.xlsx'), 'template-despesas.xlsx');
    }

    /**
     * Salva o arquivo referente a fatura
     * @param integer $fileId
     * @return void
     */
    public function storeFile(Request $request)
    {
        // $this->creditCardInvoiceService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }

    /**
     * Remove o arquivo referente a fatura
     * @param integer $fileId
     * @return void
     */
    public function deleteFile(int $fileId)
    {
        // $this->creditCardInvoiceService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
