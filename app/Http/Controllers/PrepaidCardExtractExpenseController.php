<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\PrepaidCardExtractExpenseService;
use App\Services\Interfaces\PrepaidCardExtractExpenseServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrepaidCardExtractExpenseController extends Controller
{
    public function __construct(private PrepaidCardExtractExpenseServiceInterface $prepaidCardExtractExpenseService)
    {
    }

    /**
     * Cria uma Despesa para uma Fatura do cartão de credito
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
     * Atualiza uma Despesa para uma Fatura do cartão de credito
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
     * Deleta uma Despesa de uma Fatura do cartão de credito
     */
    public function delete(int $id)
    {
        $this->prepaidCardExtractExpenseService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
