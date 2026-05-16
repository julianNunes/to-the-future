<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Budget\CloneBudgetRequest;
use App\Http\Requests\Budget\StoreBudgetRequest;
use App\Http\Requests\Budget\UpdateBudgetRequest;
use App\Services\Interfaces\BudgetServiceInterface;
use Inertia\Inertia;

class BudgetController extends Controller
{
    public function __construct(private BudgetServiceInterface $budgetService)
    {
    }

    /**
     * Return data to view Budget
     */
    public function index(string $year)
    {
        $data = $this->budgetService->index($year);
        return Inertia::render('Budget/Index', $data);
    }

    /**
     * Create Budget with your relations
     */
    public function store(StoreBudgetRequest $request)
    {
        $data = $request->validated();

        $this->budgetService->createComplete(
            auth()->user()->id,
            $data['year'],
            $data['month'],
            $data['start_week_1'] ?? null,
            $data['end_week_1'] ?? null,
            $data['start_week_2'] ?? null,
            $data['end_week_2'] ?? null,
            $data['start_week_3'] ?? null,
            $data['end_week_3'] ?? null,
            $data['start_week_4'] ?? null,
            $data['end_week_4'] ?? null,
            $data['automaticGenerateYear'] ?? false,
            $data['includeFixExpenses'] ?? false,
            $data['includeProvisions'] ?? false
        );

        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Clone a Budget with your relations
     * @param integer $id
     */
    public function clone(CloneBudgetRequest $request, int $id)
    {
        $data = $request->validated();

        $this->budgetService->clone(
            $id,
            $data['year'],
            $data['month'],
            $data['includeProvisions'] ?? false,
            $data['cloneBugdetExpenses'] ?? false,
            $data['cloneBugdetIncomes'] ?? false,
            $data['cloneBugdetGoals'] ?? false
        );

        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Update a Budget
     * @param integer $id
     */
    public function update(UpdateBudgetRequest $request, int $id)
    {
        $data = $request->validated();

        $this->budgetService->update(
            $id,
            $data['start_week_1'] ?? null,
            $data['end_week_1'] ?? null,
            $data['start_week_2'] ?? null,
            $data['end_week_2'] ?? null,
            $data['start_week_3'] ?? null,
            $data['end_week_3'] ?? null,
            $data['start_week_4'] ?? null,
            $data['end_week_4'] ?? null,
            $data['closed'] ?? false
        );
        return redirect()->back()->with('success', 'default.sucess-update');
    }

    /**
     * Delete a Budget
     * @param integer $id
     */
    public function delete(int $id)
    {
        $this->budgetService->delete($id);
        return redirect()->back()->with('success', 'default.sucess-delete');
    }

    /**
     * Find a Budget by year and month
     * @param string $year
     * @param string $month
     */
    public function findByYearMonth(string $year, string $month)
    {
        $budget = $this->budgetService->findByYearMonth(
            $year,
            $month
        );

        return to_route('budget.show', ['id' => $budget->id]);
    }

    /**
     * Show data to the view
     * @param integer $id
     */
    public function show(int $id)
    {
        $data = $this->budgetService->show(
            $id
        );
        return Inertia::render('Budget/Show', $data);
    }

    /**
     * Include in Budget all the Fix Expenses
     * @param integer $id
     */
    public function includeFixExpenses(int $id)
    {
        $data = $this->budgetService->includeFixExpenses(
            $id
        );
        return redirect()->back()->with('success', 'default.sucess-save');
    }

    /**
     * Include in Budget all the default Provisions
     * @param integer $id
     */
    public function includeProvisions(int $id)
    {
        $data = $this->budgetService->includeProvisions(
            $id
        );
        return redirect()->back()->with('success', 'default.sucess-save');
    }
}
