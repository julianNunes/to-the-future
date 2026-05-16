<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreditCardInvoiceExpense\StoreCreditCardInvoiceExpenseRequest;
use App\Http\Requests\CreditCardInvoiceExpense\StoreImportCreditCardInvoiceExpenseRequest;
use App\Http\Requests\CreditCardInvoiceExpense\UpdateCreditCardInvoiceExpenseRequest;
use App\Services\Interfaces\CreditCardInvoiceExpenseServiceInterface;

class CreditCardInvoiceExpenseController extends Controller
{
    public function __construct(private CreditCardInvoiceExpenseServiceInterface $creditCardInvoiceExpenseService) {}

    /**
     * Create new Expense to Invoice and your portions
     */
    public function store(StoreCreditCardInvoiceExpenseRequest $request)
    {
        $payload = $request->validated();

        if (($payload['portion_total'] ?? null) && $payload['portion_total'] >= 2) {
            $this->creditCardInvoiceExpenseService->createWithPortions(
                $payload['credit_card_id'],
                $payload['invoice_id'],
                $payload['description'],
                $payload['date'],
                (float) $payload['value'],
                $payload['group'],
                $payload['portion'],
                $payload['portion_total'],
                $payload['remarks'] ?? null,
                $payload['share_value'] ?? null,
                $payload['share_user_id'] ?? null,
                \collect($payload['tags'] ?? []),
                \collect($payload['divisions'] ?? [])
            );
        } else {
            $this->creditCardInvoiceExpenseService->create(
                $payload['credit_card_id'],
                $payload['invoice_id'],
                $payload['description'],
                $payload['date'],
                (float) $payload['value'],
                $payload['group'],
                $payload['portion'] ?? null,
                $payload['portion_total'] ?? null,
                $payload['remarks'] ?? null,
                $payload['share_value'] ?? null,
                $payload['share_user_id'] ?? null,
                \collect($payload['tags'] ?? []),
                \collect($payload['divisions'] ?? [])
            );
        }

        return \redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     */

    /**
     * Update a Expense
     * @param integer $id
     */
    public function update(UpdateCreditCardInvoiceExpenseRequest $request, int $id)
    {
        $payload = $request->validated();

        $this->creditCardInvoiceExpenseService->update(
            $id,
            $payload['credit_card_id'],
            $payload['invoice_id'],
            $payload['description'],
            $payload['date'],
            (float) $payload['value'],
            $payload['group'],
            $payload['portion'] ?? null,
            $payload['portion_total'] ?? null,
            $payload['remarks'] ?? null,
            $payload['share_value'] ?? null,
            $payload['share_user_id'] ?? null,
            \collect($payload['tags'] ?? []),
            \collect($payload['divisions'] ?? [])
        );

        return \redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Delete a Expense
     * @param integer $id
     */
    public function delete(int $id)
    {
        $this->creditCardInvoiceExpenseService->delete($id);
        return \redirect()->back()->with('success', 'default.sucess-delete');
    }

    /**
     * Delete all Expense portions of a Invoice Credit Card
     * @param integer $id
     */
    public function deletePortions(int $id)
    {
        $this->creditCardInvoiceExpenseService->deletePortions($id);
        return \redirect()->back()->with('success', 'default.sucess-delete');
    }

    /**
     * Read data from Excel and save the Expenses
     */
    public function storeImportExcel(StoreImportCreditCardInvoiceExpenseRequest $request)
    {
        $payload = $request->validated();

        $this->creditCardInvoiceExpenseService->storeImportExcel($payload['invoice_id'], \collect($payload['data']));
        return \redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Search by description. Used in the "v-auto-complete" component
     * @param string $description
     * @return void
     */
    public function search(string $description)
    {
        $data = $this->creditCardInvoiceExpenseService->search($description);
        return \response()->json($data);
    }
}
