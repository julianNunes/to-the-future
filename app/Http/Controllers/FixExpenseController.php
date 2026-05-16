<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\FixExpense\StoreFixExpenseRequest;
use App\Http\Requests\FixExpense\UpdateFixExpenseRequest;
use App\Services\Interfaces\FixExpenseServiceInterface;
use Inertia\Inertia;

class FixExpenseController extends Controller
{
    public function __construct(private FixExpenseServiceInterface $fixExpenseService)
    {
    }

    /**
     * Returns data for the Fixed Expense index
     */
    public function index()
    {
        $data = $this->fixExpenseService->index();
        return Inertia::render('FixExpense/Index', $data);
    }

    /**
     * Create a new Fix Expense
     */
    public function store(StoreFixExpenseRequest $request)
    {
        $data = $request->validated();

        $this->fixExpenseService->create(
            $data['description'],
            $data['due_date'],
            $data['value'],
            $data['remarks'] ?? null,
            $data['share_value'] ?? null,
            $data['share_user_id'] ?? null,
            collect($data['tags'] ?? [])
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a Fix Expense
     * @param int $id
     */
    public function update(UpdateFixExpenseRequest $request, int $id)
    {
        $data = $request->validated();

        $this->fixExpenseService->update(
            $id,
            $data['description'],
            $data['due_date'],
            $data['value'],
            $data['remarks'] ?? null,
            $data['share_value'] ?? null,
            $data['share_user_id'] ?? null,
            collect($data['tags'] ?? [])
        );
        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Deleta a Fix Expense
     * @param int $id
     */
    public function delete(int $id)
    {
        $this->fixExpenseService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
