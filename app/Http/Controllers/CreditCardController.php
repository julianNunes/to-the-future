<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\CreditCardServiceInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CreditCardController extends Controller
{
    public function __construct(private CreditCardServiceInterface $creditCardService)
    {
    }

    /**
     * Returns data to Credit Card Management
     */
    public function index()
    {
        $data = $this->creditCardService->index();
        return Inertia::render('CreditCard/Index', $data);
    }

    /**
     * Create new Credit Card
     * @param Request $request
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
     * Update a Credit Card
     * @param Request $request
     * @param integer $id
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
     * Deleta a Credit Card
     * @param integer $id
     */
    public function delete(int $id)
    {
        $this->creditCardService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
