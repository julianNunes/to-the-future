<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreditCard\StoreCreditCardRequest;
use App\Http\Requests\CreditCard\UpdateCreditCardRequest;
use App\Services\Interfaces\CreditCardServiceInterface;
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
     */
    public function store(StoreCreditCardRequest $request)
    {
        $data = $request->validated();

        $this->creditCardService->create(
            $data['name'],
            $data['digits'],
            $data['due_date'],
            $data['closing_date'],
            $data['is_active']
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a Credit Card
     * @param integer $id
     */
    public function update(UpdateCreditCardRequest $request, int $id)
    {
        $data = $request->validated();

        $this->creditCardService->update(
            $id,
            $data['name'],
            $data['digits'],
            $data['due_date'],
            $data['closing_date'],
            $data['is_active']
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
