<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\FinancingService;
use App\Services\Interfaces\FinancingServiceInterface;
use Illuminate\Http\Request;
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
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'description' => ['required'],
            'start_date' => ['required'],
            'total' => ['required'],
            'portion_total' => ['required'],
            // 'remarks' => ['required'],
            'start_date_installment' => ['required'],
            'value_installment' => ['required'],
        ]);

        $this->financingService->create(
            $request->description,
            $request->start_date,
            floatval($request->total),
            $request->fees_monthly ? floatval($request->fees_monthly) : null,
            intval($request->portion_total),
            $request->start_date_installment,
            floatval($request->value_installment),
            $request->remarks
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Edit a Financing. Only some data are available.
     * Updating of installments will only occur in installments that are open
     * @param Request $request
     * @param integer $id
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'description' => ['required'],
            'start_date' => ['required'],
            'total' => ['required'],
            // 'remarks' => ['required'],
        ]);

        $this->financingService->update(
            $id,
            $request->description,
            $request->start_date,
            floatval($request->total),
            $request->fees_monthly ? floatval($request->fees_monthly) : null,
            $request->value_installment ? floatval($request->value_installment) : null,
            $request->remarks
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
