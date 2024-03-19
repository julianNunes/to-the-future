<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\PrepaidCardExtractExpenseServiceInterface;
use Illuminate\Http\Request;

class PrepaidCardExtractExpenseController extends Controller
{
    public function __construct(private PrepaidCardExtractExpenseServiceInterface $prepaidCardExtractExpenseService)
    {
    }

    /**
     * Create a new Expense Prepaid Card
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'prepaid_card_id' => ['required'],
            'extract_id' => ['required'],
            'description' => ['required'],
            'date' => ['required'],
            'value' => ['required'],
        ]);

        $this->prepaidCardExtractExpenseService->create(
            $request->prepaid_card_id,
            $request->extract_id,
            $request->description,
            $request->date,
            floatval($request->value),
            $request->group,
            $request->remarks,
            $request->share_value ? floatval($request->share_value) : null,
            $request->share_user_id,
            collect($request->tags)
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a Expense Prepaid Card
     * @param Request $request
     * @param integer $id
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'prepaid_card_id' => ['required'],
            'extract_id' => ['required'],
            'description' => ['required'],
            'date' => ['required'],
            'value' => ['required'],
        ]);

        $this->prepaidCardExtractExpenseService->update(
            $id,
            $request->prepaid_card_id,
            $request->extract_id,
            $request->description,
            $request->date,
            floatval($request->value),
            $request->group,
            $request->remarks,
            $request->share_value ? floatval($request->share_value) : null,
            $request->share_user_id,
            collect($request->tags)
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
}
