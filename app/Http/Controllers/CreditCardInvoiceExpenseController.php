<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\CreditCardInvoiceExpenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditCardInvoiceExpenseController extends Controller
{
    protected $creditCardInvoiceExpenseService;

    public function __construct(CreditCardInvoiceExpenseService $creditCardInvoiceExpenseService)
    {
        $this->creditCardInvoiceExpenseService = $creditCardInvoiceExpenseService;
    }

    /**
     * Cria uma Despesa para uma fatura
     */
    public function store(Request $request, int $creditCardId, int $invoiceId)
    {
        $this->validate($request, [
            'description' => ['required'],
            'date' => ['required'],
            'value' => ['required'],
        ]);

        DB::beginTransaction();

        $this->creditCardInvoiceExpenseService->create(
            $creditCardId,
            $invoiceId,
            $request->description,
            $request->date,
            floatval($request->value),
            $request->group,
            $request->portion,
            $request->portion_total,
            $request->remarks,
            $request->share_value ? floatval($request->share_value) : null,
            $request->share_user_id,
            collect($request->tags)
        );

        DB::commit();

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Atualiza uma Despesa para uma fatura
     */
    public function update(Request $request, int $creditCardId, int $invoiceId, int $id)
    {
        $this->validate($request, [
            'description' => ['required'],
            'date' => ['required'],
            'value' => ['required'],
        ]);

        $this->creditCardInvoiceExpenseService->update(
            $id,
            $creditCardId,
            $invoiceId,
            $request->description,
            $request->date,
            floatval($request->value),
            $request->group,
            $request->portion,
            $request->portion_total,
            $request->remarks,
            $request->share_value ? floatval($request->share_value) : null,
            $request->share_user_id,
            collect($request->tags)
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Deleta uma Desoesa de uma Fatura do cartão de credito
     */
    public function destroy(int $creditCardId, int $invoiceId, int $id)
    {
        $this->creditCardInvoiceExpenseService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
