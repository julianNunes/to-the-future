<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Financing\UpdateFinancingInstallmentRequest;
use App\Services\Interfaces\FinancingInstallmentServiceInterface;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FinancingInstallmentController extends Controller
{

    public function __construct(private FinancingInstallmentServiceInterface $financingInstallmentService)
    {
    }

    /**
     * Returns data for Financing Installment Management
     * @param integer $financingId
     */
    public function index(int $financingId): Response
    {
        $data = $this->financingInstallmentService->index($financingId);
        return Inertia::render('Financing/Show', $data);
    }

    /**
     * Update a Installment of Financing
     * @param integer $id
     */
    public function update(UpdateFinancingInstallmentRequest $request, int $id): RedirectResponse
    {
        $data = $request->validated();

        $this->financingInstallmentService->update(
            $id,
            $data['date'],
            $data['value'],
            $data['paid'],
            $data['payment_date'] ?? null,
            $data['paid_value'] ?? null,
        );

        return redirect()->back()->with('success', 'default.sucess-update');
    }
}
