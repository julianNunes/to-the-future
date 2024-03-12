<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\PrepaidCardExtractServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PrepaidCardExtractController extends Controller
{
    public function __construct(private PrepaidCardExtractServiceInterface $prepaidCardExtractService)
    {
    }

    /**
     * Returns data for Prepaid Card Extract Management
     * @param integer $prepaidCardId
     * @return void
     */
    public function index(int $prepaidCardId)
    {
        $data = $this->prepaidCardExtractService->index($prepaidCardId);
        return Inertia::render('PrepaidCardExtract/Index', $data);
    }

    /**
     * Create a new Extract
     * @param Request $request
     * @return void
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'year' => ['required'],
            'month' => ['required'],
            'balance' => ['required'],
            'prepaid_card_id' => ['required'],
        ]);

        $this->prepaidCardExtractService->create(
            $request->prepaid_card_id,
            $request->year,
            $request->month,
            floatval($request->balance),
            $request->remarks
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a Extract
     * @param Request $request
     * @param integer $id
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'balance' => ['required'],
            'prepaid_card_id' => ['required'],
        ]);

        $this->prepaidCardExtractService->update(
            $id,
            floatval($request->balance),
            $request->remarks,
        );
        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Deleta uma Fatura
     * @param integer $id
     * @return void
     */
    public function delete(int $id)
    {
        $this->prepaidCardExtractService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }

    /**
     * Mostrar os dados de uma fatura
     * @param integer $prepaidCardId
     * @param integer $id
     * @return void
     */
    public function show(int $id)
    {
        $data = $this->prepaidCardExtractService->show($id);
        return Inertia::render('PrepaidCardExtract/Show', $data);
    }
}
