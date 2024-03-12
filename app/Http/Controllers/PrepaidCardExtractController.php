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
     * Retorna os dados para o index de Faturas de um Cartão de Credito
     * @param integer $creditCardId
     * @return void
     */
    public function index(int $creditCardId)
    {
        $data = $this->prepaidCardExtractService->index($creditCardId);
        return Inertia::render('PrepaidCardExtract/Index', $data);
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

        $this->prepaidCardExtractService->createAutomatic(
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
        $this->prepaidCardExtractService->delete($id);
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
        $data = $this->prepaidCardExtractService->show($id);
        return Inertia::render('PrepaidCardExtract/Show', $data);
    }
}
