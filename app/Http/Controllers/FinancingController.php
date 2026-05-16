<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Financing\StoreFinancingRequest;
use App\Http\Requests\Financing\UpdateFinancingRequest;
use App\Services\Interfaces\FinancingServiceInterface;
use Inertia\Inertia;

class FinancingController extends Controller
{
    public function __construct(private FinancingServiceInterface $financingService)
    {
    }

    /**
     * Returns data to Financial Management
     */
    public function index()
    {
        $data = $this->financingService->index();
        return Inertia::render('Financing/Index', $data);
    }

    /**
     */

    /**
     * Create a new Financing amd your installments
     */
    public function store(StoreFinancingRequest $request)
    {
        $data = $request->validated();

        $this->financingService->create(
            $data['description'],
            $data['start_date'],
            $data['total'],
            $data['fees_monthly'],
            $data['portion_total'],
            $data['start_date_installment'],
            $data['value_installment'],
            $data['remarks'] ?? null
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Edit a Financing. Only some data are available.
     * Updating of installments will only occur in installments that are open
     * @param integer $id
     */
    public function update(UpdateFinancingRequest $request, int $id)
    {
        $data = $request->validated();

        $this->financingService->update(
            $id,
            $data['description'],
            $data['start_date'],
            $data['total'],
            $data['fees_monthly'],
            $data['value_installment'] ?? null,
            $data['remarks'] ?? null
        );

        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Deleta a Financing
     * @param integer $id
     */
    public function delete(int $id)
    {
        $this->financingService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
