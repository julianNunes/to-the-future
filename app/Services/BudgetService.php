<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\BudgetExpense;
use App\Models\BudgetGoal;
use App\Models\BudgetProvision;
use App\Models\FixExpense;
use App\Models\Provision;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;

class BudgetService
{
    public function __construct()
    {
    }

    /**
     * Undocumented function
     *
     * @param string $year
     * @return array
     */
    public function index(string $year): array
    {
        $budgets = Budget::where(['user_id', auth()->user()->id, 'year' => $year])->get();

        return [
            'budgets' => $budgets,
        ];
    }


    public function create(
        string $year,
        string $month,
        bool $automaticGenerateYear = false,
        bool $includeFixExpense = false,
        bool $includeProvision = false,
        Collection $goals = null
    ): Budget {
        $budget = Budget::where(['year', $year, 'month' => $month, 'user_id', auth()->user()->id])->first();

        if ($budget) {
            throw new Exception('budget.already-exists');
        }

        $fix_expenses = null;
        $provisions = null;
        $date = Carbon::parse($year . '-' . $month . '-01');

        $budget = new Budget([
            'year' => $year,
            'month' => $month,
            'user_id' => auth()->user()->id
        ]);

        $budget->save();

        if ($includeFixExpense) {
            $fix_expenses = FixExpense::where('user_id', auth()->user()->id)->get();

            if ($fix_expenses && $fix_expenses->count()) {
                $new_date = $date->copy();

                foreach ($fix_expenses as $expense) {
                    $new_date = $date->day($expense->due_date);

                    BudgetExpense::create([
                        'description' => $expense,
                        'date' => $new_date->format('y-m-d'),
                        'value' => $expense->value,
                        'remarks' => $expense->remarks,
                        'share_value' => $expense->share_value,
                        'share_user_id' => $expense->share_user_id,
                        'budget_id' => $budget->id
                    ]);
                }
            }
        }

        if ($includeProvision) {
            $provisions = Provision::where('user_id', auth()->user()->id)->get();

            if ($provisions && $provisions->count()) {
                foreach ($provisions as $provision) {
                    BudgetProvision::create([
                        'description' => $provision->description,
                        'value' => $provision->value,
                        'group' => $provision->group,
                        'remarks' => $provision->remarks,
                        'share_value' => $provision->share_value,
                        'share_user_id' => $provision->share_user_id,
                        'budget_id' => $budget->id
                    ]);
                }
            }
        }

        if ($goals && $goals->count()) {
            foreach ($goals as $goal) {
                $new_goal = BudgetGoal::create([
                    'description' => $goal->description,
                    'value' => $goal->value,
                    'group' => $goal->group,
                    'count_only_share' => $goal->count_only_share,
                    'budget_id' => $budget->id
                ]);

                $tags = collect($goal->tags);

                if ($tags && $tags->count()) {
                    TagService::saveTagsToModel($tags, $new_goal);
                }
            }
        }

        if ($automaticGenerateYear) {
            $new_date = $date->copy()->addMonth();

            for ($i = $new_date->month; $i <= 12; $i++) {
                $new_budget = Budget::where(['year' => $year, 'month' => $new_date->month, 'user_id' => auth()->user()->id])->first();

                if (!$new_budget) {
                    $new_budget = new Budget([
                        'year' => $year,
                        'month' => $new_date->format('m'),
                        'user_id' => auth()->user()->id
                    ]);

                    $new_budget->save();

                    if ($includeFixExpense && $fix_expenses && $fix_expenses->count()) {
                        foreach ($fix_expenses as $expense) {
                            $new_date = $date->day($expense->due_date);

                            BudgetExpense::create([
                                'description' => $expense,
                                'date' => $new_date->format('y-m-d'),
                                'value' => $expense->value,
                                'remarks' => $expense->remarks,
                                'share_value' => $expense->share_value,
                                'share_user_id' => $expense->share_user_id,
                                'budget_id' => $new_budget->id
                            ]);
                        }
                    }

                    if ($includeProvision && $provisions && $provisions->count()) {
                        foreach ($provisions as $provision) {
                            BudgetProvision::create([
                                'description' => $provision->description,
                                'value' => $provision->value,
                                'group' => $provision->group,
                                'remarks' => $provision->remarks,
                                'share_value' => $provision->share_value,
                                'share_user_id' => $provision->share_user_id,
                                'budget_id' => $new_budget->id
                            ]);
                        }
                    }

                    if ($goals && $goals->count()) {
                        foreach ($goals as $goal) {
                            $new_goal = BudgetGoal::create([
                                'description' => $goal->description,
                                'value' => $goal->value,
                                'group' => $goal->group,
                                'count_only_share' => $goal->count_only_share,
                                'budget_id' => $new_budget->id
                            ]);

                            $tags = collect($goal->tags);

                            if ($tags && $tags->count()) {
                                TagService::saveTagsToModel($tags, $new_goal);
                            }
                        }
                    }
                }

                $new_date->addMonth();
            }
        }

        return $budget;
    }


    public function update(
        int $id,
        string $name,
        string $digits,
        string $dueDate,
        string $closingDate,
        bool $isActive
    ): bool {
        $tag = Budget::where('name', $name)->where('id', '!=', $id)->first();

        if ($tag) {
            throw new Exception('credit-card.already-exists');
        }

        $credit_card = Budget::find($id);

        if (!$credit_card) {
            throw new Exception('credit-card.not-found');
        }

        return $credit_card->update([
            'name' => $name,
            'digits' => $digits,
            'due_date' => $dueDate,
            'closing_date' => $closingDate,
            'is_active' => $isActive,
            'user_id' => auth()->user()->id
        ]);
    }


    public function delete(int $id): bool
    {
        $credit_card = Budget::with([
            'invoices' => [
                'file',
                'expenses' => [
                    'divisions' => [
                        'tags'
                    ],
                    'tags'
                ]
            ]
        ])->find($id);


        if (!$credit_card) {
            throw new Exception('credit-card.not-found');
        }

        // Remove todos os vinculos
        foreach ($credit_card->invoices as $invoice) {
            foreach ($invoice->expenses as $expense) {
                foreach ($expense->divisions as $division) {
                    if ($division->tags && $division->tags->count()) {
                        $division->tags()->detach();
                    }

                    $division->delete();
                }

                if ($expense->tags && $expense->tags->count()) {
                    $expense->tags()->detach();
                }

                $expense->delete();
            }

            if ($invoice->file) {
                $invoice->file->delete();
            }
        }

        return $credit_card->delete();
    }
}
