<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\CreditCardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CreditCardController extends Controller
{

    protected $creditCardService;

    public function __construct(CreditCardService $creditCardService)
    {
        $this->creditCardService = $creditCardService;
    }

    /**
     * Retorna os dados para o index de Cartão de Credito
     */
    public function index()
    {
        $data = $this->creditCardService->index();
        return Inertia::render('CreditCard/Index', $data);
    }


    /**
     * Cria um novo Cartão de Credito
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required'],
            'digits' => ['required'],
            'due_date' => ['required'],
            'closing_date' => ['required'],
            'is_active' => ['required'],
        ]);

        $this->creditCardService->create(
            $request->name,
            $request->digits,
            $request->due_date,
            $request->closing_date,
            $request->is_active == '1'
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Atualiza um Cartão de Credito
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'name' => ['required'],
            'digits' => ['required'],
            'due_date' => ['required'],
            'closing_date' => ['required'],
            'is_active' => ['required'],
        ]);

        $this->creditCardService->update(
            $id,
            $request->name,
            $request->digits,
            $request->due_date,
            $request->closing_date,
            $request->is_active == '1'
        );
        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Deleta um Cartão de Credito
     */
    public function delete(int $id)
    {
        $this->creditCardService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
