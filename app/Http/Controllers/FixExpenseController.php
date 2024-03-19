<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\FixExpenseServiceInterface;
use Illuminate\Http\Request;
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
     * @param Request $request
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'description' => ['required'],
            'due_date' => ['required'],
            'value' => ['required'],
        ]);

        $this->fixExpenseService->create(
            $request->description,
            $request->due_date,
            floatval($request->value),
            $request->remarks,
            $request->share_value ? floatval($request->share_value) : null,
            $request->share_user_id,
            collect($request->tags)
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Update a Fix Expense
     * @param Request $request
     * @param int $id
     */
    public function update(Request $request, int $id)
    {
        $this->validate($request, [
            'description' => ['required'],
            'due_date' => ['required'],
            'value' => ['required'],
        ]);

        $this->fixExpenseService->update(
            $id,
            $request->description,
            $request->due_date,
            floatval($request->value),
            $request->remarks,
            $request->share_value,
            $request->share_user_id,
            collect($request->tags)
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
