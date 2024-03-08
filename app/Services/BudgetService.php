<?php

namespace App\Services;

use App\Helpers\Budget\Interfaces\BudgetCalculateInterface;
use App\Helpers\Budget\Interfaces\BudgetShowDataInterface;
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
        private BudgetShowDataInterface $budgetShowData,
        private BudgetCalculateInterface $budgetCalculate,
        // Repositories
        private FixExpenseRepositoryInterface $fixExpenseRepository,
        private ProvisionRepositoryInterface $provisionRepository,
        private BudgetRepositoryInterface $budgetRepository,
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
        $date = Carbon::parse($year . '-' . $month . '-01');

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
                        $budget->id,
                        $expense->description,
                        $new_date->day($expense->due_date)->format('y-m-d'),
                        $expense->value,
                        'MONTHLY',
                        $expense->remarks,
                        false,
                        $expense->share_value,
                        $expense->share_user_id,
                        null,
                        $expense->tags
                    );
                }
            }
        }

        if ($includeProvision) {
            $provisions = $this->provisionRepository->get(['user_id' => $userId], [], [], ['tags']);

            if ($provisions && $provisions->count()) {
                foreach ($provisions as $provision) {
                    $this->budgetProvisionService->create(
                        $budget->id,
                        $provision->description,
                        $provision->value,
                        $provision->group,
                        $provision->remarks,
                        $provision->share_value,
                        $provision->share_user_id,
                        $provision->tags
                    );
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
                                $new_budget->id,
                                $expense->description,
                                $due_date,
                                $expense->value,
                                'MONTHLY',
                                $expense->remarks,
                                false,
                                $expense->share_value,
                                $expense->share_user_id,
                                null,
                                $expense->tags
                            );
                        }
                    }

                    if ($includeProvision && $provisions && $provisions->count()) {
                        foreach ($provisions as $provision) {
                            $this->budgetProvisionService->create(
                                $new_budget->id,
                                $provision->description,
                                $provision->value,
                                $provision->group,
                                $provision->remarks,
                                $provision->share_value,
                                $provision->share_user_id,
                                $provision->tags
                            );
                        }
                    }
                }

                $this->budgetCalculate->recalculate($new_budget->id);

                // if ($has_share_fix_expense || $has_share_provision) {
                //     if ($shareUser) {
                //         try {
                //             $this->create($shareUser->share_user_id, $year, $new_date->month);
                //         } catch (Exception $e) {
                //         }
                //     }
                // }

                $new_date->addMonth();
            }
        }

        $this->budgetCalculate->recalculate($budget->id);

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
            $with->push('goals.tag');
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
                    $expense->group,
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
        $data = $this->budgetShowData->dataShow($id);
        return $data;
    }

    /**
     * Include in Budget all the Fix Expenses
     * @param integer $id
     * @return boolean
     */
    public function includeFixExpenses(int $id): bool
    {
        $budget = $this->budgetRepository->show($id);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        $date = Carbon::parse($budget->year . '-' . $budget->month . '-01');

        $fix_expenses = $this->fixExpenseRepository->get(['user_id' => $budget->user_id], [], [], ['tags']);

        if (!$fix_expenses || !$fix_expenses->count()) {
            throw new Exception('budget-show.all-fix-expenses-not-found');
        }

        if ($fix_expenses && $fix_expenses->count()) {
            $new_date = $date->copy();

            foreach ($fix_expenses as $expense) {
                $this->budgetExpenseService->create(
                    $budget->id,
                    $expense->description,
                    $new_date->day($expense->due_date)->format('y-m-d'),
                    $expense->value,
                    'MONTHLY',
                    $expense->remarks,
                    false,
                    $expense->share_value,
                    $expense->share_user_id,
                    null,
                    $expense->tags
                );
            }
        }

        return true;
    }

    /**
     * Include in Budget all the default Provisions
     * @param integer $id
     * @return boolean
     */
    public function includeProvisions(int $id): bool
    {
        $budget = $this->budgetRepository->show($id, ['provisions']);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        $provisions = $this->provisionRepository->get(['user_id' => $budget->user_id], [], [], ['tags']);

        if (!$provisions || !$provisions->count()) {
            throw new Exception('budget-show.all-provision-not-found');
        }

        if ($provisions && $provisions->count()) {
            foreach ($provisions as $provision) {
                $this->budgetProvisionService->create(
                    $budget->id,
                    $provision->description,
                    $provision->value,
                    $provision->group,
                    $provision->remarks,
                    $provision->share_value,
                    $provision->share_user_id,
                    $provision->tags
                );
            }
        }

        return true;
    }
}
