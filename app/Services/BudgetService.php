<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\BudgetExpense;
use App\Models\BudgetGoal;
use App\Models\BudgetIncome;
use App\Models\BudgetProvision;
use App\Models\CreditCardInvoice;
use App\Models\FinancingInstallment;
use App\Models\FixExpense;
use App\Models\Provision;
use App\Models\ShareUser;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;

class BudgetService
{
    protected $budgetExpenseService;
    protected $budgetProvisionService;
    protected $budgetIncomeService;
    protected $budgetGoalService;

    public function __construct()
    {
        $this->budgetExpenseService = new BudgetExpenseService();
        $this->budgetProvisionService = new BudgetProvisionService();
        $this->budgetIncomeService = new BudgetIncomeService();
        $this->budgetGoalService = new BudgetGoalService();
    }

    /**
     * Undocumented function
     *
     * @param string $year
     * @return array
     */
    public function index(string $year): array
    {
        $budgets = Budget::where(['user_id' => auth()->user()->id, 'year' => $year])->get();
        return [
            'budgets' => $budgets,
            'year' => $year,
        ];
    }

    /**
     * Undocumented function
     *
     * @param integer $userId
     * @param string $year
     * @param string $month
     * @param boolean $automaticGenerateYear
     * @param boolean $includeFixExpense
     * @param boolean $includeProvision
     * @return Budget
     */
    public function create(
        int $userId,
        string $year,
        string $month,
        bool $automaticGenerateYear = false,
        bool $includeFixExpense = false,
        bool $includeProvision = false
    ): Budget {
        $budget = Budget::where(['year' => $year, 'month' => $month, 'user_id' => $userId])->first();

        if ($budget) {
            throw new Exception('budget.already-exists');
        }

        $fix_expenses = null;
        $provisions = null;
        $has_share_fix_expense = false;
        $has_share_provision = false;
        $date = Carbon::parse($year . '-' . $month . '-01');
        $shareUser = ShareUser::where('user_id', $userId)->first();

        $budget = new Budget([
            'year' => $year,
            'month' => $month,
            'user_id' => $userId
        ]);

        $budget->save();

        if ($includeFixExpense) {
            $fix_expenses = FixExpense::with(['tags'])->where('user_id', $userId)->get();

            if ($fix_expenses && $fix_expenses->count()) {
                $new_date = $date->copy();

                foreach ($fix_expenses as $expense) {
                    $this->budgetExpenseService->create(
                        $expense->description,
                        $new_date->day($expense->due_date)->format('y-m-d'),
                        $expense->value,
                        $expense->remarks,
                        $budget->id,
                        $expense->share_value,
                        $expense->share_user_id,
                        $expense->tags
                    );
                }

                $has_share_fix_expense = $fix_expenses->whereNotNull('share_user_id')->count() > 0 ? true : false;
            }
        }

        if ($includeProvision) {
            $provisions = Provision::with(['tags'])->where('user_id', $userId)->get();

            if ($provisions && $provisions->count()) {
                foreach ($provisions as $provision) {
                    $this->budgetProvisionService->create(
                        $provision->description,
                        $provision->value,
                        $provision->group,
                        $provision->remarks,
                        $budget->id,
                        $provision->share_value,
                        $provision->share_user_id,
                        $provision->tags
                    );
                }
            }

            $has_share_provision = $provisions->whereNotNull('share_user_id')->count() > 0 ? true : false;
        }

        if ($has_share_fix_expense || $has_share_provision) {
            if ($shareUser) {
                try {
                    $this->create($shareUser->share_user_id, $year, $month);
                } catch (Exception $e) {
                }
            }
        }

        if ($automaticGenerateYear) {
            $new_date = $date->copy()->addMonth();

            for ($i = $new_date->month; $i <= 12; $i++) {
                $new_budget = Budget::where(['year' => $year, 'month' => $new_date->month, 'user_id' => $userId])->first();

                if (!$new_budget) {
                    $new_budget = new Budget([
                        'year' => $year,
                        'month' => $new_date->format('m'),
                        'user_id' => $userId
                    ]);

                    $new_budget->save();

                    if ($includeFixExpense && $fix_expenses && $fix_expenses->count()) {
                        foreach ($fix_expenses as $expense) {
                            $due_date = $new_date->copy()->day($expense->due_date)->format('y-m-d');

                            $this->budgetExpenseService->create(
                                $expense->description,
                                $due_date,
                                $expense->value,
                                $expense->remarks,
                                $new_budget->id,
                                $expense->share_value,
                                $expense->share_user_id,
                                $expense->tags
                            );
                        }
                    }

                    if ($includeProvision && $provisions && $provisions->count()) {
                        foreach ($provisions as $provision) {
                            $this->budgetProvisionService->create(
                                $provision->description,
                                $provision->value,
                                $provision->group,
                                $provision->remarks,
                                $new_budget->id,
                                $provision->share_value,
                                $provision->share_user_id,
                                $provision->tags
                            );
                        }
                    }
                }

                $this->recalculateBugdet($new_budget->id);

                if ($has_share_fix_expense || $has_share_provision) {
                    if ($shareUser) {
                        try {
                            $this->create($shareUser->share_user_id, $year, $new_date->month);
                        } catch (Exception $e) {
                        }
                    }
                }

                $new_date->addMonth();
            }
        }

        $this->recalculateBugdet($budget->id);

        return $budget;
    }

    /**
     * Undocumented function
     *
     * @param integer $id
     * @param string $year
     * @param string $month
     * @param boolean $includeProvision
     * @param boolean $cloneBugdetExpense
     * @param boolean $cloneBugdetIncome
     * @param boolean $cloneBugdetGoals
     * @return Budget
     */
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
                $query->with(['goals.tags']);
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
                    $this->budgetProvisionService->create(
                        $provision->description,
                        $provision->value,
                        $provision->group,
                        $provision->remarks,
                        $new_budget->id,
                        $provision->share_value,
                        $provision->share_user_id,
                        $provision->tags
                    );
                }
            }
        }

        if ($cloneBugdetExpense && $budget->expenses && $budget->expenses->count()) {
            foreach ($budget->expenses as $expense) {
                $this->budgetExpenseService->create(
                    $expense->description,
                    $expense->date,
                    $expense->value,
                    $expense->remarks,
                    $new_budget->id,
                    $expense->share_value,
                    $expense->share_user_id,
                    $expense->tags
                );
            }
        }

        if ($cloneBugdetIncome && $budget->incomes && $budget->incomes->count()) {
            foreach ($budget->incomes as $income) {
                $this->budgetIncomeService->create(
                    $income->description,
                    $income->date,
                    $income->value,
                    $income->remarks,
                    $new_budget->id,
                    $income->tags
                );
            }
        }

        if ($cloneBugdetGoals && $budget->goals && $budget->goals->count()) {
            foreach ($budget->goals as $goal) {
                $this->budgetGoalService->create(
                    $goal->description,
                    $goal->value,
                    $goal->group,
                    $goal->count_only_share,
                    $budget->id,
                    $goal->tags
                );
            }
        }

        return $budget;
    }

    /**
     * Undocumented function
     *
     * @param integer $id
     * @param boolean $closed
     * @return boolean
     */
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
            throw new Exception('budget.not-found');
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

    /**
     *
     * @param integer $id
     * @return void
     */
    public function recalculateBugdet(int $id)
    {
        // Busca o budget do id
        // com despesas, receitas e provisionamento
        $budget = Budget::with([
            'expenses',
            'incomes',
            'provisions',
        ])->find($id);

        if (!$budget) {
            throw new Exception('budget.not-found');
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
            $total_income += $budget->expenses->sum('share_value');
        }

        // Soma Provisionamento
        if ($budget->provisions && $budget->provisions->count()) {
            $total_expense += $budget->provisions->sum('value');
            $total_income += $budget->provisions->sum('share_value');
        }

        $credit_card_invoices = CreditCardInvoice::with(['expenses'])
            ->where(['year' => $budget->year, 'month' => $budget->month])
            ->whereHas('creditCard', function (Builder $query) use ($budget) {
                $query->where('user_id', $budget->user_id);
            })
            ->get();

        if ($credit_card_invoices && $credit_card_invoices->count()) {
            foreach ($credit_card_invoices as $invoice) {
                if ($invoice->expenses && $invoice->expenses->count()) {
                    $total_expense += $invoice->expenses->sum('value');
                    $total_income += $invoice->expenses->sum('share_value');
                }
            }
        }

        // busca share user
        $share_user = ShareUser::where('user_id', $budget->user_id)->with('shareUser')->first();

        if ($share_user) {
            // Busca orçamento com usuario compartilhado
            $budget_share = Budget::with([
                'expenses' => function ($query) use ($budget) {
                    $query->where('share_user_id', $budget->user_id);
                },
                'provisions' => function ($query) use ($budget) {
                    $query->where('share_user_id', $budget->user_id);
                },
            ])
                ->where(['year' => $budget->year, 'month' => $budget->month, 'user_id' => $share_user->share_user_id])
                ->where(function (Builder $query) use ($budget) {
                    $query->whereHas('expenses', function (Builder $query2) use ($budget) {
                        $query2->where('share_user_id', $budget->user_id);
                    })
                        ->orWhereHas('provisions', function (Builder $query2) use ($budget) {
                            $query2->where('share_user_id', $budget->user_id);
                        });
                })
                ->first();

            if ($budget_share) {
                // Soma Despesas
                if ($budget_share->expenses && $budget_share->expenses->count()) {
                    $total_expense += $budget_share->expenses->sum('share_value');
                }

                // Soma Provisionamento
                if ($budget_share->provisions && $budget_share->provisions->count()) {
                    $total_expense += $budget_share->provisions->sum('share_value');
                }
            }

            $credit_card_invoices = CreditCardInvoice::with([
                'expenses' => function (Builder $query) use ($budget) {
                    $query->where('share_user_id', $budget->user_id);
                }
            ])
                ->where(['year' => $budget->year, 'month' => $budget->month])
                ->whereHas('creditCard', function (Builder $query) use ($share_user) {
                    $query->where('user_id', $share_user->share_user_id);
                })
                ->whereHas('expenses', function (Builder $query) use ($budget) {
                    $query->where('share_user_id', $budget->user_id);
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


    /**
     * Undocumented function
     *
     * @param string $year
     * @param string $month
     * @return Budget
     */
    public function findByYearMonth(string $year, string $month): Budget
    {
        $budget = Budget::where(['user_id' => auth()->user()->id, 'year' => $year, 'month' => $month])->first();

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        return $budget;
    }

    public function show(int $id)
    {
        // Busca o budget do id
        // com despesas, receitas e provisionamento
        $budget = Budget::with([
            'expenses' => [
                'tags',
                'shareUser',
                'financingInstallment'
            ],
            'incomes.tags',
            'provisions.tags',
            'goals.tags',
        ])->find($id);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        // Busca as parcelas de Financiamento em aberto para mostrar no select em tela

        $installments = FinancingInstallment::with([
            'financing' => function (Builder $query) {
                $query->where('paid', false);
            }
        ])
            ->whereHas('financing', function (Builder $query) use ($budget) {
                $query->where('user_id', $budget->user_id)->addSelect(['id', 'description']);
            })
            ->get();

        // Busca as faturas dos cartões
        $credit_card_invoices = CreditCardInvoice::with([
            'creditCard',
            'file',
            'expenses' => [
                'tags',
                'shareUser',
                'divisions' => [
                    'tags',
                    'shareUser'
                ]
            ]
        ])
            ->where(['year' => $budget->year, 'month' => $budget->month])
            ->whereHas('creditCard', function (Builder $query) use ($budget) {
                $query->where('user_id', $budget->user_id);
            })
            ->get();

        // busca share user
        $share_users = ShareUser::where('user_id', $budget->user_id)->with('shareUser')->get();
        $share_user = null;

        if ($share_users && $share_users->count()) {
            $share_user = $share_users->first();
        }

        $budget_share = null;
        $credit_card_invoices_share = null;

        if ($share_user) {
            // Busca orçamento com usuario compartilhado
            $budget_share = Budget::with([
                'expenses.tags',
                'incomes.tags',
                'provisions.tags',
                'goals.tags',
            ])
                ->where(['year' => $budget->year, 'month' => $budget->month, 'user_id' => $share_user->share_user_id])
                ->first();

            $credit_card_invoices_share = CreditCardInvoice::with([
                'creditCard',
                'file',
                'expenses' => [
                    'tags',
                    'shareUser',
                    'divisions' => [
                        'tags',
                        'shareUser'
                    ]
                ]
            ])
                ->where(['year' => $budget->year, 'month' => $budget->month])
                ->whereHas('creditCard', function (Builder $query) use ($share_user) {
                    $query->where('user_id', $share_user->share_user_id);
                })
                ->get();
        }

        /**
         * @todo Montar as Metas e dados para o grafico
         */

        /**
         * @todo Painel de Despesas
         */
        //  - Painel de Despesas (COLUNA 12)
        //  - Na tabela de despesas ter o botão "+" para expandir a linha quando a despesa for uma parcela do financiamento
        //  - Lançamento de Despesa
        //      - Se existe "user_share_id" e valor a compartilhar:
        //          - Gero um registro virtual na minha receite
        //          - gero um registr virtual na despesa do compartilhado
        //          - Painel de Receitas (COLUNA 6)

        if (!$budget->expenses) {
            $budget->expenses = collect();
        }

        // Gera os totalizadores dos cartões de credito para Despesas
        if ($credit_card_invoices && $credit_card_invoices->count()) {
            foreach ($credit_card_invoices as $invoice) {
                $budget->expenses->push([
                    'id' => null,
                    'description' => 'Total ' . $invoice->creditCard->name,
                    'date' => $invoice->due_date,
                    'value' => $invoice->expenses->sum('value'),
                    'remarks' => '',
                    'paid' => $invoice->total_paid ? true : false,
                    'share_value' => $invoice->expenses->sum('share_value'),
                    'share_user_id' => $share_user ? $share_user->share_user_id : null,
                    'tags' => []
                ]);
            }
        }

        // Verifica os valores compartilhados com usuario logado
        // Verifica os valores das despesas do Orcamento do usuario compartilhado
        if ($budget_share && $budget_share->expenses && $budget_share->expenses->count() && $budget_share->expenses->whereNotNull('share_user_id')->count()) {
            $filtered = $budget_share->expenses->whereNotNull('share_user_id');

            foreach ($filtered as $expense) {
                $budget->expenses->push([
                    'id' => null,
                    'description' => $expense->description,
                    'date' => $expense->date,
                    'value' => $expense->share_value,
                    'remarks' => 'budget.share-expense',
                    'paid' => $expense->paid,
                    'share_value' => null,
                    'share_user_id' => null,
                    'tags' => []
                ]);
            }
        }

        // Verifica as faturas do cartao de credito do usuario compartilhado
        if ($credit_card_invoices_share && $credit_card_invoices_share->count()) {
            foreach ($credit_card_invoices_share as $invoice) {
                $filtered = $invoice->expenses->filter(function ($expense) {
                    return $expense->share_user_id || ($expense->divisions && $expense->divisions->count() && $expense->divisions->whereNotNull('share_user_id')->count());
                });

                foreach ($filtered as $expense) {
                    $budget->expenses->push([
                        'id' => null,
                        'description' => 'Total ' . $invoice->creditCard->name,
                        'date' => $invoice->due_date,
                        'value' => $invoice->expenses->sum('value'),
                        'remarks' => 'budget.share-expense',
                        'paid' => $invoice->total_paid ? true : false,
                        'share_value' => $invoice->expenses->sum('share_value'),
                        'share_user_id' => $share_user ? $share_user->share_user_id : null,
                        'tags' => []
                    ]);
                }
            }
        }

        // Montar as Receitas

        // - Painel de Resumo dos Cartões (COLUNA 6)
        //     - Parcelados
        //     - Provisionamento
        //     - Semana 1, 2, 3, 4

        // - Painel de Resumo de pagamento/recebimento do usuario compartilhado  (COLUNA 6)
        //      - Somar o valor que devo pagar para o "share_user_id"
        //      - Somar o valor a receber do "share_user_id"

        // - Expanse-painel com o componente de Cartões
        //     - Foreach com todos os dados
        //     - AJUSTE DO TITULO com nome do cartão


        // - Expanse-painel com o componente para Provisionamento

        return [
            'budget' => $budget,
            'budgetShare' => $budget_share,
            'shareUser' => $share_user->shareUser,
            'shareUsers' => $installments,
            'installments' => $installments,
        ];
    }
}
