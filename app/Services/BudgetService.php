<?php

namespace App\Services;

use App\Models\Budget;
use App\Repositories\Interfaces\{
    BudgetExpenseRepositoryInterface,
    BudgetGoalRepositoryInterface,
    BudgetIncomeRepositoryInterface,
    BudgetProvisionRepositoryInterface,
    BudgetRepositoryInterface,
    CreditCardInvoiceRepositoryInterface,
    FinancingInstallmentRepositoryInterface,
    FixExpenseRepositoryInterface,
    ProvisionRepositoryInterface,
    ShareUserRepositoryInterface,
};
use App\Services\Interfaces\{
    BudgetExpenseServiceInterface,
    BudgetGoalServiceInterface,
    BudgetIncomeServiceInterface,
    BudgetProvisionServiceInterface,
    BudgetServiceInterface,
};
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;

class BudgetService implements BudgetServiceInterface
{
    public function __construct(
        // Services
        private BudgetExpenseServiceInterface $budgetExpenseService,
        private BudgetProvisionServiceInterface $budgetProvisionService,
        private BudgetIncomeServiceInterface $budgetIncomeService,
        private BudgetGoalServiceInterface $budgetGoalService,
        // Repositories
        private FixExpenseRepositoryInterface $fixExpenseRepository,
        private ProvisionRepositoryInterface $provisionRepository,
        private BudgetRepositoryInterface $budgetRepository,
        private ShareUserRepositoryInterface $shareUserRepository,
        private CreditCardInvoiceRepositoryInterface $creditCardInvoiceRepository,
        private FinancingInstallmentRepositoryInterface $financingInstallmentRepository,
        private BudgetExpenseRepositoryInterface $budgetExpenseRepository,
        private BudgetProvisionRepositoryInterface $budgetProvisionRepository,
        private BudgetIncomeRepositoryInterface $budgetIncomeRepository,
        private BudgetGoalRepositoryInterface $budgetGoalRepository,
    ) {
    }

    /**
     * Return data to view Budget
     * @param string $year
     * @return array
     */
    public function index(string $year): array
    {
        $budgets = $this->budgetRepository->get(['user_id' => auth()->user()->id, 'year' => $year]);
        return [
            'budgets' => $budgets,
            'year' => $year,
        ];
    }

    /**
     * Create Budget with your relations
     * @param integer $userId
     * @param string $year
     * @param string $month
     * @param boolean $automaticGenerateYear
     * @param boolean $includeFixExpense
     * @param boolean $includeProvision
     * @return Budget
     */
    public function createComplete(
        int $userId,
        string $year,
        string $month,
        bool $automaticGenerateYear = false,
        bool $includeFixExpense = false,
        bool $includeProvision = false
    ): Budget {
        $budget = $this->create(
            $userId,
            $year,
            $month
        );

        $fix_expenses = null;
        $provisions = null;
        $has_share_fix_expense = false;
        $has_share_provision = false;
        $date = Carbon::parse($year . '-' . $month . '-01');
        $shareUser = $this->shareUserRepository->getOne(['user_id' => $userId]);

        // Verificar se existem faturas ja criadas para o mes/ano do orçamento
        $credit_card_invoices = $this->creditCardInvoiceRepository->get(
            function (Builder $query) use ($budget) {
                $query
                    ->where(['year' => $budget->year, 'month' => $budget->month, 'budget_id' => null])
                    ->whereHas('creditCard', function (Builder $query) use ($budget) {
                        $query->where('user_id', $budget->user_id)->where('is_active', true);
                    });
            },
            [],
            [],
            []
        );

        foreach ($credit_card_invoices as $invoice) {
            $this->creditCardInvoiceRepository->store(['budget_id' => $budget->id], $invoice);
        }

        if ($includeFixExpense) {
            $fix_expenses = $this->fixExpenseRepository->get(['user_id' => $userId], [], [], ['tags']);

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
            $provisions = $this->provisionRepository->get(['user_id' => $userId], [], [], ['tags']);

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
                $new_budget = $this->budgetRepository->getOne(['year' => $year, 'month' => $new_date->month, 'user_id' => $userId]);

                if (!$new_budget) {
                    $new_budget = $this->budgetRepository->store([
                        'year' => $year,
                        'month' => $new_date->format('m'),
                        'user_id' => $userId
                    ]);

                    // Verificar se existem faturas ja criadas para o mes/ano do orçamento
                    $credit_card_invoices = $this->creditCardInvoiceRepository->get(
                        function (Builder $query) use ($new_budget) {
                            $query
                                ->where(['year' => $new_budget->year, 'month' => $new_budget->month, 'budget_id' => null])
                                ->whereHas('creditCard', function (Builder $query) use ($new_budget) {
                                    $query->where('user_id', $new_budget->user_id)->where('is_active', true);
                                });
                        },
                        [],
                        [],
                        []
                    );

                    foreach ($credit_card_invoices as $invoice) {
                        $this->creditCardInvoiceRepository->store(['budget_id' => $budget->id], $invoice);
                    }

                    foreach ($credit_card_invoices as $invoice) {
                        $this->creditCardInvoiceRepository->store(['budget_id' => $budget->id], $invoice);
                    }

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
     * Create a new Budget
     * @param integer $userId
     * @param string $year
     * @param string $month
     * @return Budget
     */
    public function create(
        int $userId,
        string $year,
        string $month
    ): Budget {
        $budget = $this->budgetRepository->getOne(['year' => $year, 'month' => $month, 'user_id' => $userId]);

        if ($budget) {
            throw new Exception('budget.already-exists');
        }

        return $this->budgetRepository->store([
            'year' => $year,
            'month' => $month,
            'user_id' => $userId
        ]);
    }

    /**
     * Clone a Budget with your relations
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
        $budget = $this->budgetRepository->getOne(['year', $year, 'month' => $month, 'user_id', auth()->user()->id]);

        if ($budget) {
            throw new Exception('budget.already-exists');
        }

        $with = collect();

        if ($cloneBugdetExpense) {
            $with->push(['expenses' => function (Builder $query2) {
                $query2->whereNull('financing_installment_id')->with(['tags']);
            }]);
        }

        if ($cloneBugdetIncome) {
            $with->push('incomes.tags');
        }

        if ($cloneBugdetGoals) {
            $with->push('goals.tags');
        }

        $budget = $this->budgetRepository->show($id, $with->toArray());

        // $budget = Budget::when($cloneBugdetExpense, function (Builder $query) {
        //     $query->with([
        //         'expenses' => function (Builder $query2) {
        //             $query2->whereNull('financing_installment_id')->with(['tags']);
        //         }
        //     ]);
        // })
        //     ->when($cloneBugdetIncome, function (Builder $query) {
        //         $query->with(['incomes.tags']);
        //     })
        //     ->when($cloneBugdetGoals, function (Builder $query) {
        //         $query->with(['goals.tags']);
        //     })
        //     ->find($id);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        $provisions = null;
        $new_budget = $this->create(
            $year,
            $month,
            auth()->user()->id
        );

        if ($includeProvision) {
            $provisions = $this->provisionRepository->get(['user_id' => auth()->user()->id], [], [], ['tags']);

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
     * Update a Budget
     * @param integer $id
     * @param boolean $closed
     * @return boolean
     */
    public function update(
        int $id,
        bool $closed
    ): Budget {
        $budget = $this->budgetRepository->show($id);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        return $this->budgetRepository->store(['closed' => $closed], $budget);
    }

    /**
     * Delete a Budget
     * @param integer $id
     * @return boolean
     */
    public function delete(int $id): bool
    {
        $budget = $this->budgetRepository->show($id, [
            'expenses.tags',
            'incomes.tags',
            'provisions.tags',
            'goals'
        ]);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        // Despesas
        foreach ($budget->expenses as $expense) {
            $this->budgetExpenseService->delete($expense->id);
        }

        // Receitas
        foreach ($budget->incomes as $income) {
            $this->budgetIncomeService->delete($income->id);
        }

        // Metas
        foreach ($budget->goals as $goal) {
            $this->budgetGoalService->delete($goal->id);
        }

        return $this->budgetRepository->delete($id);
    }

    /**
     * Find a Budget by year and month
     * @param string $year
     * @param string $month
     * @return Budget
     */
    public function findByYearMonth(string $year, string $month): Budget
    {
        $budget = $this->budgetRepository->getOne(['user_id' => auth()->user()->id, 'year' => $year, 'month' => $month]);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        return $budget;
    }

    /**
     * Show data to the view
     * @param integer $id
     * @return array
     */
    public function show(int $id): array
    {
        // Busca o budget do id
        // com despesas, receitas e provisionamento
        $budget = $this->budgetRepository->show($id, [
            'expenses' => [
                'tags',
                'shareUser',
                'financingInstallment' => [
                    'financing:id,description'
                ]
            ],
            'incomes.tags',
            'provisions' => [
                'tags',
                'shareUser',
            ],
            'goals.tags',
        ]);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        // Busca as parcelas de Financiamento em aberto para mostrar no select em tela
        $installments = $this->financingInstallmentRepository->get(
            function (Builder $query) use ($budget) {
                $query->whereHas('financing', function (Builder $query) use ($budget) {
                    $query->where('user_id', $budget->user_id);
                })
                    ->doesntHave('budgetExpense')
                    ->where('paid', false);
            },
            [],
            [],
            ['financing:id,description']
        );

        // Buscar faturas com despesas
        $credit_card_invoices = $this->creditCardInvoiceRepository->get(
            function (Builder $query) use ($budget) {
                $query
                    ->where(['year' => $budget->year, 'month' => $budget->month])
                    ->whereHas('creditCard', function (Builder $query) use ($budget) {
                        $query->where('user_id', $budget->user_id)->where('is_active', true);
                    });
            },
            [],
            [],
            [
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
            ]
        );

        $share_users = $this->shareUserRepository->get(['user_id' => $budget->user_id], ['shareUser', 'user']);
        $share_user = null;

        if ($share_users && $share_users->count()) {
            $share_user = $share_users->first();
            $share_users = $share_users->map(function ($item) {
                return [
                    'share_user_id' => $item->share_user_id,
                    'share_user_name' => $item->shareUser->name
                ];
            });
        }

        $budget_share = null;
        $credit_card_invoices_share = null;

        if ($share_user) {
            // Busca orçamento com usuario compartilhado
            $budget_share = $this->budgetRepository->getOne(
                ['year' => $budget->year, 'month' => $budget->month, 'user_id' => $share_user->share_user_id],
                [
                    'expenses' => [
                        'tags',
                        'shareUser',
                        'financingInstallment' => [
                            'financing:id,description'
                        ]
                    ],
                    'incomes.tags',
                    'provisions' => [
                        'tags',
                        'shareUser',
                    ],
                    'goals.tags',
                ]
            );

            $credit_card_invoices_share = $this->creditCardInvoiceRepository->get(
                function (Builder $query) use ($budget, $share_user) {
                    $query
                        ->where(['year' => $budget->year, 'month' => $budget->month])
                        ->whereHas('creditCard', function (Builder $query) use ($share_user) {
                            $query->where('user_id', $share_user->share_user_id)->where('is_active', true);
                        });
                },
                [],
                [],
                [
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
                ]
            );

            if ($budget_share) {
                if (!$budget_share->expenses) {
                    $budget_share->expenses = collect();
                }

                if (!$budget_share->incomes) {
                    $budget_share->incomes = collect();
                }
            }
        }

        /**
         * Painel de Despesas e Receitas
         */
        if (!$budget->expenses) {
            $budget->expenses = collect();
        }

        if (!$budget->incomes) {
            $budget->incomes = collect();
        }

        // Gera o totalizador para Provisionamento para Despesas
        if ($budget->provisions && $budget->provisions->count()) {
            $share_value = $budget->provisions->sum('share_value');
            $budget->expenses->push([
                'id' => null,
                'description' => 'budget-expense.total-provision',
                'date' => null,
                'value' => $budget->provisions->sum('value'),
                'remarks' => '',
                'paid' => null,
                'share_value' => $share_value,
                'share_user_id' => $share_value && $share_user ? $share_user->share_user_id : null,
                'tags' => []
            ]);

            if ($share_value > 0 && $budget_share) {
                $budget_share->expenses->push([
                    'id' => null,
                    'description' => 'budget-expense.total-provision',
                    'date' => null,
                    'value' => $share_value,
                    'remarks' => '',
                    'paid' => null,
                    'share_value' => $share_value,
                    'share_user_id' => $share_value && $share_user ? $share_user->share_user_id : null,
                    'tags' => []
                ]);
            }
        }

        // Gera os totalizadores dos cartões de credito para Despesas
        if ($credit_card_invoices && $credit_card_invoices->count()) {
            foreach ($credit_card_invoices as $invoice) {
                $share_value = $invoice->expenses->sum('share_value');
                $budget->expenses->push([
                    'id' => null,
                    'description' => 'Total: ' . $invoice->creditCard->name,
                    'date' => $invoice->due_date,
                    'value' => $invoice->expenses->sum('value'),
                    'remarks' => '',
                    'paid' => $invoice->total_paid ? true : false,
                    'share_value' => $share_value,
                    'share_user_id' => $share_value && $share_user ? $share_user->share_user_id : null,
                    'tags' => []
                ]);
            }
        }

        // Verifica os valores compartilhados com usuario logado
        // Verifica os valores das despesas do Orcamento do usuario compartilhado
        if ($budget_share && $budget_share->expenses && $budget_share->expenses->count() && $budget_share->expenses->where('share_user_id', $budget->user_id)->count()) {
            $filtered = $budget_share->expenses->where('share_user_id', $budget->user_id);

            foreach ($filtered as $expense) {
                $budget->expenses->push([
                    'id' => null,
                    'description' => $expense->description,
                    'date' => $expense->date,
                    'value' => $expense->share_value,
                    'remarks' => 'budget-expense.share-expense',
                    'paid' => null,
                    'share_value' => null,
                    'share_user_id' => null,
                    'tags' => []
                ]);

                $budget_share->incomes->push([
                    'id' => null,
                    'description' => $expense->description,
                    'date' => null,
                    'value' => $expense->share_value,
                    'remarks' => 'budget-expense.share-expense',
                ]);
            }
        }

        // Verifica os valores do Provisionamento do Orcamento do usuario compartilhado
        if ($budget_share && $budget_share->provisions && $budget_share->provisions->count() && $budget_share->provisions->where('share_user_id', $budget->user_id)->count()) {
            $filtered = $budget_share->provisions->where('share_user_id', $budget->user_id);
            $budget->expenses->push([
                'id' => null,
                'description' => 'budget-expense.total-share-provision',
                'date' => null,
                'value' => $filtered->sum('share_value'),
                'remarks' => 'budget-expense.share-expense',
                'paid' => null,
                'share_value' => null,
                'share_user_id' => null,
                'tags' => []
            ]);

            $budget_share->incomes->push([
                'id' => null,
                'description' => 'budget-expense.total-share-provision',
                'date' => null,
                'value' => $filtered->sum('share_value'),
                'remarks' => 'budget-expense.share-expense',
            ]);
        }

        // Verifica as faturas do cartao de credito do usuario compartilhado para adicionar nas Despesas
        if ($credit_card_invoices_share && $credit_card_invoices_share->count()) {
            foreach ($credit_card_invoices_share as $invoice) {
                $filtered = $invoice->expenses->filter(function ($expense) use ($budget) {
                    return $expense->share_user_id == $budget->user_id || ($expense->divisions && $expense->divisions->count() && $expense->divisions->where('share_user_id', $budget->user_id)->count());
                });

                if ($filtered && $filtered->count()) {
                    $budget->expenses->push([
                        'id' => null,
                        'description' => 'Total: ' . $invoice->creditCard->name,
                        'date' => $invoice->due_date,
                        'value' => $filtered->sum('share_value'),
                        'remarks' => 'budget-expense.share-expense',
                        'paid' => null,
                        'share_value' => null,
                        'share_user_id' => null,
                        'tags' => []
                    ]);

                    $budget_share->incomes->push([
                        'id' => null,
                        'description' => 'Total: ' . $invoice->creditCard->name,
                        'date' => null,
                        'value' => $filtered->sum('share_value'),
                        'remarks' => 'budget-expense.share-expense',
                    ]);
                }
            }
        }

        /**
         * Gera o totalizador para o usuario compartilhado
         */
        if ($budget_share && $share_user) {
            if (!$budget_share->expenses) {
                $budget_share->expenses = collect();
            }

            // Gera o totalizador para Provisionamento para Despesas do usuario compartilhado
            if ($budget_share->provisions && $budget_share->provisions->count()) {
                $share_value = $budget_share->provisions->sum('share_value');
                $budget_share->expenses->push([
                    'id' => null,
                    'description' => 'budget-expense.total-provision',
                    'date' => null,
                    'value' => $budget_share->provisions->sum('value'),
                    'remarks' => '',
                    'paid' => null,
                    'share_value' => $share_value,
                    'share_user_id' => $share_value ? $budget->user_id : null,
                    'tags' => []
                ]);
            }

            // Gera os totalizadores dos cartões de credito para Despesas
            if ($credit_card_invoices_share && $credit_card_invoices_share->count()) {
                foreach ($credit_card_invoices_share as $invoice) {
                    $share_value = $invoice->expenses->sum('share_value');
                    $budget_share->expenses->push([
                        'id' => null,
                        'description' => 'Total: ' . $invoice->creditCard->name,
                        'date' => $invoice->due_date,
                        'value' => $invoice->expenses->sum('value'),
                        'remarks' => '',
                        'paid' => $invoice->total_paid ? true : false,
                        'share_value' => $share_value,
                        'share_user_id' => $share_value ? $budget->user_id : null,
                        'tags' => []
                    ]);
                }
            }

            // Verifica os valores para o usuario logado
            // Verifica os valores das despesas do Orcamento do usuario logado
            if ($budget && $budget->expenses && $budget->expenses->count() && $budget->expenses->where('share_user_id', $budget_share->user_id)->count()) {
                $filtered = $budget_share->expenses->where('share_user_id', $budget_share->user_id);

                foreach ($filtered as $expense) {
                    $budget_share->expenses->push([
                        'id' => null,
                        'description' => $expense->description,
                        'date' => $expense->date,
                        'value' => $expense->share_value,
                        'remarks' => 'budget-expense.share-expense',
                        'paid' => null,
                        'share_value' => null,
                        'share_user_id' => null,
                        'tags' => []
                    ]);
                }
            }

            // Verifica os valores do Provisionamento do Orcamento do usuario compartilhado
            if ($budget && $budget->provisions && $budget->provisions->count() && $budget->provisions->where('share_user_id', $budget_share->user_id)->count()) {
                $filtered = $budget->provisions->where('share_user_id', $budget_share->user_id);
                $budget_share->expenses->push([
                    'id' => null,
                    'description' => 'budget-expense.total-provision',
                    'date' => null,
                    'value' => $filtered->sum('share_value'),
                    'remarks' => 'budget-expense.share-expense',
                    'paid' => null,
                    'share_value' => null,
                    'share_user_id' => null,
                    'tags' => []
                ]);
            }

            // Verifica as faturas do cartao de credito do usuario compartilhado para adicionar nas Despesas
            if ($credit_card_invoices && $credit_card_invoices->count()) {
                foreach ($credit_card_invoices as $invoice) {
                    $filtered = $invoice->expenses->filter(function ($expense) use ($budget_share) {
                        return $expense->share_user_id == $budget_share->user_id || ($expense->divisions && $expense->divisions->count() && $expense->divisions->where('share_user_id', $budget_share->user_id)->count());
                    });

                    if ($filtered && $filtered->count()) {
                        $budget_share->expenses->push([
                            'id' => null,
                            'description' => 'Total: ' . $invoice->creditCard->name,
                            'date' => $invoice->due_date,
                            'value' => $filtered->sum('share_value'),
                            'remarks' => 'budget-expense.share-expense',
                            'paid' => null,
                            'share_value' => null,
                            'share_user_id' => null,
                            'tags' => []
                        ]);
                    }
                }
            }
        }

        /**
         * @todo Montar as Metas e dados para o grafico
         */

        $goals_charts = [];

        // Painel de Despesas por Tag

        // Painel de Metas

        // - Painel de Resumo dos Cartões (COLUNA 6)
        //     - Parcelados
        //     - Provisionamento
        //     - Semana 1, 2, 3, 4

        // - Painel de Resumo de pagamento/recebimento do usuario compartilhado  (COLUNA 6)
        //      - Somar o valor que devo pagar para o "share_user_id"
        //      - Somar o valor a receber do "share_user_id"

        return [
            'shareUser' => $share_user->shareUser,
            'shareUsers' => $share_users,
            'installments' => $installments,
            'budget' => $budget,
            'creditCardInvoices' => $credit_card_invoices,
            'budgetShare' => $budget_share,
            'creditCardInvoicesShare' => $credit_card_invoices_share,
            'installmentsShare' => $installments,
            'goalsCharts' => $goals_charts,
        ];
    }

    /**
     * Recalculate the totals to Budget
     * @param integer $id
     * @return Budget
     */
    public function recalculateBugdet(int $id): Budget
    {
        // Busca o budget do id
        // com despesas, receitas e provisionamento
        $budget = $this->budgetRepository->show($id, [
            'expenses',
            'incomes',
            'provisions',
        ]);

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

        $credit_card_invoices = $this->creditCardInvoiceRepository->get(
            function (Builder $query) use ($budget) {
                $query
                    ->where(['year' => $budget->year, 'month' => $budget->month])
                    ->whereHas('creditCard', function (Builder $query2) use ($budget) {
                        $query2->where('user_id', $budget->user_id);
                    });
            },
            [],
            [],
            ['expenses']
        );

        if ($credit_card_invoices && $credit_card_invoices->count()) {
            foreach ($credit_card_invoices as $invoice) {
                if ($invoice->expenses && $invoice->expenses->count()) {
                    $total_expense += $invoice->expenses->sum('value');
                    $total_income += $invoice->expenses->sum('share_value');
                }
            }
        }

        // busca share user
        $share_user = $this->shareUserRepository->getOne(['user_id' => $budget->user_id], ['shareUser']);

        if ($share_user) {
            $budget_share = $this->budgetRepository->getOne(
                function (Builder $query) use ($budget, $share_user) {
                    $query->where(['year' => $budget->year, 'month' => $budget->month, 'user_id' => $share_user->share_user_id])
                        ->where(function (Builder $query) use ($budget) {
                            $query->whereHas('expenses', function (Builder $query2) use ($budget) {
                                $query2->where('share_user_id', $budget->user_id);
                            })
                                ->orWhereHas('provisions', function (Builder $query2) use ($budget) {
                                    $query2->where('share_user_id', $budget->user_id);
                                });
                        });
                },
                [],
                [],
                [
                    'expenses' => function ($query) use ($budget) {
                        $query->where('share_user_id', $budget->user_id);
                    },
                    'provisions' => function ($query) use ($budget) {
                        $query->where('share_user_id', $budget->user_id);
                    },
                ]
            );

            // Busca orçamento com usuario compartilhado
            // $budget_share = Budget::with([
            //     'expenses' => function ($query) use ($budget) {
            //         $query->where('share_user_id', $budget->user_id);
            //     },
            //     'provisions' => function ($query) use ($budget) {
            //         $query->where('share_user_id', $budget->user_id);
            //     },
            // ])
            //     ->where(['year' => $budget->year, 'month' => $budget->month, 'user_id' => $share_user->share_user_id])
            //     ->where(function (Builder $query) use ($budget) {
            //         $query->whereHas('expenses', function (Builder $query2) use ($budget) {
            //             $query2->where('share_user_id', $budget->user_id);
            //         })
            //             ->orWhereHas('provisions', function (Builder $query2) use ($budget) {
            //                 $query2->where('share_user_id', $budget->user_id);
            //             });
            //     })
            //     ->first();

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

            $credit_card_invoices = $this->creditCardInvoiceRepository->get(
                function (Builder $query) use ($budget, $share_user) {
                    $query->where(['year' => $budget->year, 'month' => $budget->month])
                        ->whereHas('creditCard', function (Builder $query) use ($share_user) {
                            $query->where('user_id', $share_user->share_user_id);
                        })
                        ->whereHas('expenses', function (Builder $query) use ($budget) {
                            $query->where('share_user_id', $budget->user_id);
                        });
                },
                [],
                [],
                [
                    'expenses' => function (Builder $query) use ($budget) {
                        $query->where('share_user_id', $budget->user_id);
                    }
                ]
            );

            // $credit_card_invoices = CreditCardInvoice::with([
            //     'expenses' => function (Builder $query) use ($budget) {
            //         $query->where('share_user_id', $budget->user_id);
            //     }
            // ])
            //     ->where(['year' => $budget->year, 'month' => $budget->month])
            //     ->whereHas('creditCard', function (Builder $query) use ($share_user) {
            //         $query->where('user_id', $share_user->share_user_id);
            //     })
            //     ->whereHas('expenses', function (Builder $query) use ($budget) {
            //         $query->where('share_user_id', $budget->user_id);
            //     })
            //     ->get();

            if ($credit_card_invoices && $credit_card_invoices->count()) {
                foreach ($credit_card_invoices as $invoice) {
                    if ($invoice->expenses && $invoice->expenses->count()) {
                        $total_expense += $invoice->expenses->sum('share_value');
                    }
                }
            }
        }

        return $this->budgetRepository->store([
            'total_expense' => $total_expense,
            'total_income' => $total_income
        ], $budget);
    }
}
