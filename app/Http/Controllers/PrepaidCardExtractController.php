<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PrepaidCardExtract\StorePrepaidCardExtractRequest;
use App\Http\Requests\PrepaidCardExtract\UpdatePrepaidCardExtractRequest;
use App\Services\Interfaces\PrepaidCardExtractServiceInterface;
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
     * @param StorePrepaidCardExtractRequest $request
     * @return void
     */
    public function store(StorePrepaidCardExtractRequest $request)
    {
        $data = $request->validated();

        $this->prepaidCardExtractService->create(
            $data['prepaid_card_id'],
            $data['year'],
            $data['month'],
            $data['credit'],
            $data['credit_date'],
            $data['remarks'] ?? null,
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a Extract
     * @param UpdatePrepaidCardExtractRequest $request
     * @param integer $id
     */
    public function update(UpdatePrepaidCardExtractRequest $request, int $id)
    {
        $data = $request->validated();

        $this->prepaidCardExtractService->update(
            $id,
            $data['credit'],
            $data['credit_date'],
            $data['remarks'] ?? null,
        );
        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Deleta a Extract
     * @param integer $id
     * @return void
     */
    public function delete(int $id)
    {
        $this->prepaidCardExtractService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }

    /**
     * Returns data for viewing/editing a Prepaid Card Extract
     * @param integer $id
     */
    public function show(int $id)
    {
        $data = $this->prepaidCardExtractService->show($id);
        return Inertia::render('PrepaidCardExtract/Show', $data);
    }

    /**
     * Download xlxs file template
     * @return void
     */
    public function downloadTemplate()
    {
        return response()->download(public_path('storage/template/template-prepaid-card.xlsx'), 'template-prepaid-card.xlsx');
    }
}
