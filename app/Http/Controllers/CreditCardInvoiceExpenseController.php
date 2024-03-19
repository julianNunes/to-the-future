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
     * Create new Expense to Invoice and your portions
     * @param Request $request
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
     */

    /**
     * Update a Expense
     * @param Request $request
     * @param integer $id
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
     * Delete a Expense
     * @param integer $id
     */
    public function delete(int $id)
    {
        $this->creditCardInvoiceExpenseService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }

    /**
     * Delete all Expense portions of a Invoice Credit Card
     * @param integer $id
     */
    public function deletePortions(int $id)
    {
        $this->creditCardInvoiceExpenseService->deletePortions($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }

    /**
     * Read data from Excel and save the Expenses
     * @param Request $request
     */
    public function storeImportExcel(Request $request)
    {
        $this->validate($request, [
            'data' => ['required'],
            'invoice_id' => ['required'],
        ]);

        $this->creditCardInvoiceExpenseService->storeImportExcel(intval($request->invoice_id), collect($request->data));
        return redirect()->back()->with('success', 'default.sucess-save');
    }
}
