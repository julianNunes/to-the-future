<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\FixExpense\StoreFixExpenseRequest;
use App\Http\Requests\FixExpense\UpdateFixExpenseRequest;
use App\Services\Interfaces\FixExpenseServiceInterface;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FixExpenseController extends Controller
{
    public function __construct(private FixExpenseServiceInterface $fixExpenseService)
    {
    }

    /**
     * Returns data for the Fixed Expense index
     */
    public function index(): Response
    {
        $data = $this->fixExpenseService->index();
        return Inertia::render('FixExpense/Index', $data);
    }

    /**
     * Create a new Fix Expense
     */
    public function store(StoreFixExpenseRequest $request): RedirectResponse
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
    public function update(UpdateFixExpenseRequest $request, int $id): RedirectResponse
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
    public function delete(int $id): RedirectResponse
    {
        $this->fixExpenseService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }
}
