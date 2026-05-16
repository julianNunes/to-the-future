<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PrepaidCardExtractExpense\StoreImportPrepaidCardExtractExpenseRequest;
use App\Http\Requests\PrepaidCardExtractExpense\StorePrepaidCardExtractExpenseRequest;
use App\Http\Requests\PrepaidCardExtractExpense\UpdatePrepaidCardExtractExpenseRequest;
use App\Services\Interfaces\PrepaidCardExtractExpenseServiceInterface;

class PrepaidCardExtractExpenseController extends Controller
{
    public function __construct(private PrepaidCardExtractExpenseServiceInterface $prepaidCardExtractExpenseService) {}

    /**
     * Create a new Expense Prepaid Card
     * @param StorePrepaidCardExtractExpenseRequest $request
     */
    public function store(StorePrepaidCardExtractExpenseRequest $request)
    {
        $data = $request->validated();

        $this->prepaidCardExtractExpenseService->create(
            $data['prepaid_card_id'],
            $data['extract_id'],
            $data['description'],
            $data['date'],
            $data['value'],
            $data['group'],
            $data['remarks'] ?? null,
            $data['share_value'] ?? null,
            $data['share_user_id'] ?? null,
            collect($data['tags'] ?? [])
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a Expense Prepaid Card
     * @param UpdatePrepaidCardExtractExpenseRequest $request
     * @param integer $id
     */
    public function update(UpdatePrepaidCardExtractExpenseRequest $request, int $id)
    {
        $data = $request->validated();

        $this->prepaidCardExtractExpenseService->update(
            $id,
            $data['prepaid_card_id'],
            $data['extract_id'],
            $data['description'],
            $data['date'],
            $data['value'],
            $data['group'],
            $data['remarks'] ?? null,
            $data['share_value'] ?? null,
            $data['share_user_id'] ?? null,
            collect($data['tags'] ?? [])
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Deleta a Expense Prepaid Card
     * @param integer $id
     */
    public function delete(int $id)
    {
        $this->prepaidCardExtractExpenseService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }

    /**
     * Read data from Excel and save the Expenses
     * @param StoreImportPrepaidCardExtractExpenseRequest $request
     */
    public function storeImportExcel(StoreImportPrepaidCardExtractExpenseRequest $request)
    {
        $data = $request->validated();

        $this->prepaidCardExtractExpenseService->storeImportExcel($data['extract_id'], collect($data['data']));
        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Search by description. Used in the "v-auto-complete" component
     * @param string $description
     * @return void
     */
    public function search(string $description)
    {
        $data = $this->prepaidCardExtractExpenseService->search($description);
        return response()->json($data);
    }
}
