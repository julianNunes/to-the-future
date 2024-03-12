<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\CreditCardInvoiceExpenseService;
use App\Services\Interfaces\CreditCardInvoiceExpenseServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditCardInvoiceExpenseController extends Controller
{
    public function __construct(private CreditCardInvoiceExpenseServiceInterface $creditCardInvoiceExpenseService)
    {
    }

    /**
     * Cria uma Despesa para uma Fatura do cartão de credito
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'credit_card_id' => ['required'],
            'invoice_id' => ['required'],
            'description' => ['required'],
            'date' => ['required'],
            'value' => ['required'],
        ]);

        if ($request->portion_total && intval($request->portion_total) >= 2) {
            $this->creditCardInvoiceExpenseService->createWithPortions(
                $request->credit_card_id,
                $request->invoice_id,
                $request->description,
                $request->date,
                floatval($request->value),
                $request->group,
                $request->portion ? intval($request->portion) : null,
                $request->portion_total ? intval($request->portion_total) : null,
                $request->remarks,
                $request->share_value ? floatval($request->share_value) : null,
                $request->share_user_id,
                collect($request->tags),
                collect($request->divisions)
            );
        } else {
            $this->creditCardInvoiceExpenseService->create(
                $request->credit_card_id,
                $request->invoice_id,
                $request->description,
                $request->date,
                floatval($request->value),
                $request->group,
                $request->portion ? intval($request->portion) : null,
                $request->portion_total ? intval($request->portion_total) : null,
                $request->remarks,
                $request->share_value ? floatval($request->share_value) : null,
                $request->share_user_id,
                collect($request->tags),
                collect($request->divisions)
            );
        }

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Atualiza uma Despesa para uma Fatura do cartão de credito
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'credit_card_id' => ['required'],
            'invoice_id' => ['required'],
            'description' => ['required'],
            'date' => ['required'],
            'value' => ['required'],
        ]);

        $this->creditCardInvoiceExpenseService->update(
            $id,
            $request->credit_card_id,
            $request->invoice_id,
            $request->description,
            $request->date,
            floatval($request->value),
            $request->group,
            $request->portion,
            $request->portion_total,
            $request->remarks,
            $request->share_value ? floatval($request->share_value) : null,
            $request->share_user_id,
            collect($request->tags),
            collect($request->divisions)
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Deleta uma Despesa de uma Fatura do cartão de credito
     */
    public function delete(int $id)
    {
        $this->creditCardInvoiceExpenseService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }

    /**
     */
    public function deletePortions(int $id)
    {
        $this->creditCardInvoiceExpenseService->deletePortions($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
