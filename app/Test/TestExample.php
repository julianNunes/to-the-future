<?php

namespace App\Test;

use App\Models\{Budget, BudgetExpense, BudgetGoal};
use App\Services\{BudgetService, TagService, UserService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class TestExample
{
    public function example(Request $request)
    {
        $budget = new Budget;
        $expense = new BudgetExpense;
        $goal = new BudgetGoal;

        $budgetService = new BudgetService;
        $tagService = new TagService;
        $userService = new UserService;

        $user = Auth::user();
        $data = DB::table('budgets')->get();

        return response()->json(['success' => true]);
    }
}
