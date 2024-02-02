<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\BudgetExpense;
use App\Models\BudgetGoal;
use App\Models\BudgetIncome;
use App\Models\BudgetProvision;
use App\Models\CreditCardInvoice;
use App\Models\FixExpense;
use App\Models\Provision;
use App\Models\ShareUser;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
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
        bool $includeProvision = false
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
            $fix_expenses = FixExpense::with(['tags'])->where('user_id', auth()->user()->id)->get();

            if ($fix_expenses && $fix_expenses->count()) {
                $new_date = $date->copy();

                foreach ($fix_expenses as $expense) {
                    $new_date = $date->day($expense->due_date);

                    $new_expense = BudgetExpense::create([
                        'description' => $expense->description,
                        'date' => $new_date->format('y-m-d'),
                        'value' => $expense->value,
                        'remarks' => $expense->remarks,
                        'share_value' => $expense->share_value,
                        'share_user_id' => $expense->share_user_id,
                        'budget_id' => $budget->id
                    ]);

                    if ($expense->tags && $expense->tags->count()) {
                        TagService::saveTagsToModel($new_expense, $expense->tags);
                    }
                }
            }
        }

        if ($includeProvision) {
            $provisions = Provision::with(['tags'])->where('user_id', auth()->user()->id)->get();

            if ($provisions && $provisions->count()) {
                foreach ($provisions as $provision) {
                    $new_provision = BudgetProvision::create([
                        'description' => $provision->description,
                        'value' => $provision->value,
                        'group' => $provision->group,
                        'remarks' => $provision->remarks,
                        'share_value' => $provision->share_value,
                        'share_user_id' => $provision->share_user_id,
                        'budget_id' => $budget->id
                    ]);

                    if ($provision->tags && $provision->tags->count()) {
                        TagService::saveTagsToModel($new_provision, $provision->tags);
                    }
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

                            $new_expense = BudgetExpense::create([
                                'description' => $expense->description,
                                'date' => $new_date->format('y-m-d'),
                                'value' => $expense->value,
                                'remarks' => $expense->remarks,
                                'share_value' => $expense->share_value,
                                'share_user_id' => $expense->share_user_id,
                                'budget_id' => $new_budget->id
                            ]);

                            if ($expense->tags && $expense->tags->count()) {
                                TagService::saveTagsToModel($new_expense, $expense->tags);
                            }
                        }
                    }

                    if ($includeProvision && $provisions && $provisions->count()) {
                        foreach ($provisions as $provision) {
                            $new_provision = BudgetProvision::create([
                                'description' => $provision->description,
                                'value' => $provision->value,
                                'group' => $provision->group,
                                'remarks' => $provision->remarks,
                                'share_value' => $provision->share_value,
                                'share_user_id' => $provision->share_user_id,
                                'budget_id' => $new_budget->id
                            ]);

                            if ($provision->tags && $provision->tags->count()) {
                                TagService::saveTagsToModel($new_provision, $provision->tags);
                            }
                        }
                    }
                }

                $new_date->addMonth();
            }
        }

        return $budget;
    }


    public function clone(
        int $id,
        string $year,
        string $month,
        bool $includeProvision = false,
        bool $cloneBugdetExpense = false,
        bool $cloneBugdetIncome = false,
        bool $cloneBugdetGoals = false
    ): Budget {
        $budget = Budget::where(['year', $year, 'month' => $month, 'user_id', auth()->user()->id])->first();

        if ($budget) {
            throw new Exception('budget.already-exists');
        }

        $budget = Budget::when($cloneBugdetExpense, function (Builder $query) {
            $query->with([
                'expenses' => function (Builder $query2) {
                    $query2->whereNull('financing_installment_id')->with(['tags']);
                }
            ]);
        })
            ->when($cloneBugdetIncome, function (Builder $query) {
                $query->with(['incomes.tags']);
            })
            ->when($cloneBugdetGoals, function (Builder $query) {
                $query->with(['goals']);
            })
            ->find($id);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        $provisions = null;
        $new_budget = new Budget([
            'year' => $year,
            'month' => $month,
            'user_id' => auth()->user()->id
        ]);

        $new_budget->save();

        if ($includeProvision) {
            $provisions = Provision::with(['tags'])->where('user_id', auth()->user()->id)->get();

            if ($provisions && $provisions->count()) {
                foreach ($provisions as $provision) {
                    $new_provision = BudgetProvision::create([
                        'description' => $provision->description,
                        'value' => $provision->value,
                        'group' => $provision->group,
                        'remarks' => $provision->remarks,
                        'share_value' => $provision->share_value,
                        'share_user_id' => $provision->share_user_id,
                        'budget_id' => $new_budget->id
                    ]);

                    if ($provision->tags && $provision->tags->count()) {
                        TagService::saveTagsToModel($new_provision, $provision->tags);
                    }
                }
            }
        }

        if ($cloneBugdetExpense && $budget->expenses && $budget->expenses->count()) {
            foreach ($budget->expenses as $expense) {
                $new_expense = BudgetExpense::create([
                    'description' => $expense->description,
                    'date' => $expense->date,
                    'value' => $expense->value,
                    'remarks' => $expense->remarks,
                    'share_value' => $expense->share_value,
                    'share_user_id' => $expense->share_user_id,
                    'budget_id' => $new_budget->id
                ]);

                if ($expense->tags && $expense->tags->count()) {
                    TagService::saveTagsToModel($new_expense, $expense->tags);
                }
            }
        }

        if ($cloneBugdetIncome && $budget->incomes && $budget->incomes->count()) {
            foreach ($budget->incomes as $income) {
                $new_income = BudgetIncome::create([
                    'description' => $income->description,
                    'date' => $income->date,
                    'value' => $income->value,
                    'remarks' => $income->remarks,
                    'share_value' => $income->share_value,
                    'share_user_id' => $income->share_user_id,
                    'budget_id' => $new_budget->id
                ]);

                if ($income->tags && $income->tags->count()) {
                    TagService::saveTagsToModel($new_income, $income->tags);
                }
            }
        }

        if ($cloneBugdetGoals && $budget->goals && $budget->goals->count()) {
            foreach ($budget->goals as $goal) {
                $new_goal = BudgetGoal::create([
                    'description' => $goal->description,
                    'value' => $goal->value,
                    'group' => $goal->group,
                    'count_only_share' => $goal->count_only_share,
                    'budget_id' => $budget->id
                ]);

                $tags = collect($goal->tags);

                if ($tags && $tags->count()) {
                    TagService::saveTagsToModel($new_goal, $tags);
                }
            }
        }

        return $budget;
    }


    public function update(
        int $id,
        bool $closed
    ): bool {
        $budget = Budget::find($id);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        return $budget->update(['closed' => $closed]);
    }

    /**
     *
     * @param integer $id
     * @return boolean
     */
    public function delete(int $id): bool
    {
        $budget = Budget::with([
            'expenses.tags',
            'incomes.tags',
            'provisions.tags',
            'goals'
        ])->find($id);


        if (!$budget) {
            throw new Exception('credit-card.not-found');
        }

        // Despesas
        foreach ($budget->expenses as $expense) {
            if ($expense->tags && $expense->tags->count()) {
                $expense->tags()->detach();
            }

            $expense->delete();
        }

        // Receitas
        foreach ($budget->incomes as $income) {
            if ($income->tags && $income->tags->count()) {
                $income->tags()->detach();
            }

            $income->delete();
        }

        // Metas
        foreach ($budget->goals as $goal) {
            $goal->delete();
        }

        return $budget->delete();
    }

    private function recalculateBugdet(int $id)
    {
        // Busca o budget do id
        // com despesas, receitas e provisionamento
        $budget = Budget::with([
            'expenses',
            'incomes',
            'provisions',
        ])->find($id);

        if (!$budget) {
            throw new Exception('credit-card.not-found');
        }

        $total_expense = 0;
        $total_income = 0;

        // Soma Receitas
        if ($budget->incomes && $budget->incomes->count()) {
            $total_income += $budget->incomes->sum('value');
        }

        // Soma Despesas
        if ($budget->expenses && $budget->expenses->count()) {
            $total_expense += $budget->expenses->sum('value');
            $total_income += $budget->expenses->sum(function (Collection $item) {
                return $item->share_value ? $item->share_value : null;
            });
        }

        // Soma Provisionamento
        if ($budget->provisions && $budget->provisions->count()) {
            $total_expense += $budget->provisions->sum('value');
            $total_income += $budget->provisions->sum(function (Collection $item) {
                return $item->share_value ? $item->share_value : null;
            });
        }

        $credit_card_invoices = CreditCardInvoice::with(['expenses'])
            ->where(['year' => $budget->year, 'month' => $budget->month])
            ->whereHas('creditCard', function (Builder $query) {
                $query->where('user_id', auth()->user()->id);
            })
            ->get();

        if ($credit_card_invoices && $credit_card_invoices->count()) {
            foreach ($credit_card_invoices as $invoice) {
                if ($invoice->expenses && $invoice->expenses->count()) {
                    $total_expense += $invoice->expenses->sum('value');
                    $total_income += $invoice->expenses->sum(function (Collection $item) {
                        return $item->share_value ? $item->share_value : null;
                    });
                }
            }
        }

        // busca share user
        $share_user = ShareUser::where('user_id', auth()->user()->id)->with('shareUser')->first();

        if ($share_user) {
            // Busca orçamento com usuario compartilhado
            $budet_share = Budget::with([
                'expenses' => function (Builder $query) {
                    $query->where('share_user_id', auth()->user()->id);
                },
                'provisions' => function (Builder $query) {
                    $query->where('share_user_id', auth()->user()->id);
                },
            ])
                ->where(['year' => $budget->year, 'month' => $budget->month, 'user_id' => $share_user->share_user_id])
                ->where(function (Builder $query) {
                    $query->whereHas('expenses.share_user_id', auth()->user()->id)
                        ->orWhereHas('provisions.share_user_id', auth()->user()->id);
                })
                ->first();

            // Soma Despesas
            if ($budet_share->expenses && $budet_share->expenses->count()) {
                $total_expense += $budet_share->expenses->sum('share_value');
            }

            // Soma Provisionamento
            if ($budet_share->provisions && $budet_share->provisions->count()) {
                $total_expense += $budet_share->provisions->sum('share_value');
            }

            $credit_card_invoices = CreditCardInvoice::with([
                'expenses' => function (Builder $query) {
                    $query->where('share_user_id', auth()->user()->id);
                }
            ])
                ->where(['year' => $budget->year, 'month' => $budget->month])
                ->whereHas('creditCard', function (Builder $query) use ($share_user) {
                    $query->where('user_id', $share_user->share_user_id);
                })
                ->whereHas('expenses', function (Builder $query) {
                    $query->where('share_user_id', auth()->user()->id);
                })
                ->get();

            if ($credit_card_invoices && $credit_card_invoices->count()) {
                foreach ($credit_card_invoices as $invoice) {
                    if ($invoice->expenses && $invoice->expenses->count()) {
                        $total_expense += $invoice->expenses->sum('share_value');
                    }
                }
            }
        }

        return $budget->update([
            'total_expense' => $total_expense,
            'total_income' => $total_income
        ]);
    }
}
