<?php

namespace App\Helpers\Budget;

use App\Helpers\Budget\Interfaces\BudgetShowDataInterface;
use App\Models\Budget;
use App\Models\BudgetExpense;
use App\Models\BudgetIncome;
use App\Models\ShareUser;
use App\Models\User;
use App\Repositories\Interfaces\{
    BudgetRepositoryInterface,
    CreditCardInvoiceRepositoryInterface,
    FinancingInstallmentRepositoryInterface,
    ShareUserRepositoryInterface,
};
use Exception;
use Illuminate\Database\Eloquent\Builder;

class BudgetShowData implements BudgetShowDataInterface
{
    public function __construct(
        // Services
        private BudgetRepositoryInterface $budgetRepository,
        private FinancingInstallmentRepositoryInterface $financingInstallmentRepository,
        private CreditCardInvoiceRepositoryInterface $creditCardInvoiceRepository,
        private ShareUserRepositoryInterface $shareUserRepository,
    ) {
    }

    /**
     * Show data to the view
     * @param integer $id
     * @return array
     */
    public function dataShow(int $id): array
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
            'invoices' => [
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

        $shareUsers = $this->shareUserRepository->get(['user_id' => $budget->user_id], [], [], ['shareUser', 'user']);
        $shareUser = null;

        if ($shareUsers && $shareUsers->count()) {
            $shareUser = $shareUsers->first();
            $shareUsers = $shareUsers->map(function ($item) {
                return [
                    'share_user_id' => $item->share_user_id,
                    'share_user_name' => $item->shareUser->name
                ];
            });
        }

        $budgetShare = null;

        if ($shareUser) {
            // Busca orçamento com usuario compartilhado
            $budgetShare = $this->budgetRepository->getOne(
                ['year' => $budget->year, 'month' => $budget->month, 'user_id' => $shareUser->share_user_id],
                [],
                [],
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
                    'invoices' => [
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
                ]
            );

            if ($budgetShare) {
                if (!$budgetShare->expenses) {
                    $budgetShare->expenses = collect();
                }

                if (!$budgetShare->incomes) {
                    $budgetShare->incomes = collect();
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

        $this->mountExpensesIncomes($budget, $budgetShare, $shareUser->shareUser);

        if ($shareUser && $budgetShare) {
            $this->mountExpensesIncomes($budgetShare, $budget, $shareUser->user);
        }

        $resume = $this->mountResume($budget, $budgetShare);
        $resume_share = null;

        if ($shareUser && $budgetShare) {
            $resume_share = $this->mountResume($budgetShare, $budget);
        }

        $goals_charts = $this->mountGoalsChart($budget, $budgetShare);
        $goals_charts_share = null;

        if ($shareUser && $budgetShare) {
            $goals_charts_share = $this->mountGoalsChart($budgetShare, $budget);
        }

        $expense_to_tags = $this->mountExpenseToTags($budget);
        $expense_to_tags_share = null;

        if ($shareUser && $budgetShare) {
            $expense_to_tags_share = $this->mountExpenseToTags($budgetShare);
        }

        return [
            'installments' => $installments,
            'shareUser' => $shareUser,
            'shareUsers' => $shareUsers,
            'owner' => [
                'budget' => $budget,
                'resume' => $resume,
                'goalsCharts' => $goals_charts,
                'expenseToTags' => $expense_to_tags,
            ],
            'share' => [
                'budget' => $budgetShare,
                'resume' => $resume_share,
                'goalsCharts' => $goals_charts_share,
                'expenseToTags' => $expense_to_tags_share,
            ]
        ];
    }

    /**
     * @param Budget $budget
     * @param Budget|null $budgetShare
     * @param User|null $shareUser
     * @return void
     */
    private function mountExpensesIncomes(Budget &$budget, Budget &$budgetShare = null, User $shareUser = null)
    {
        // Despesas
        //      Totalizador do Provisionamento
        //      Totalizador dos Cartões de credito
        //      Totalizador cartão pre pago
        //      Compartilhado com owner Despesas
        //      Compartilhado com owner Totalizador do Provisionamento
        //      Compartilhado com owner Totalizador dos cartoes
        //      Compartilhado com owner Totalizador cartão pre pago

        // Receitas
        //      Compartilhado Receitas
        //      Compartilhado Totalizador do Provisionamento
        //      Compartilhado Totalizador dos Cartões de credito
        //      Compartilhado Totalizador cartão pre pago

        // Receitas - Compartilhado Receitas
        if ($budget->expenses->count()) {
            $filtered = $budget->expenses->where('share_user_id', '!=', null)->where('id', '=', null);

            if ($filtered && $filtered->count()) {
                foreach ($filtered as $expense) {
                    $budget->incomes->push(new BudgetIncome([
                        'id' => null,
                        'description' => $expense->description,
                        'date' => null,
                        'value' => $expense->share_value,
                        'remarks' => 'budget.share-expense',
                        'tags' => collect()
                    ]));
                }
            }
        }

        // Despesas - Totalizador do Provisionamento
        if ($budget->provisions && $budget->provisions->count()) {
            $share_value = $budget->provisions->sum('share_value');
            $budget->expenses->push(new BudgetExpense([
                'id' => null,
                'description' => 'budget.total-provision',
                'date' => null,
                'value' => $budget->provisions->sum('value'),
                'remarks' => '',
                'paid' => null,
                'share_value' => $share_value,
                'share_user_id' => $share_value && $shareUser ? $shareUser->id : null,
                'tags' => collect()
            ]));

            // Receitas - Compartilhado Totalizador do Provisionamento
            if ($share_value > 0) {
                $budget->incomes->push(new BudgetIncome([
                    'id' => null,
                    'description' => 'budget.total-provision',
                    'date' => null,
                    'value' => $share_value,
                    'remarks' => 'budget.share-expense',
                    'tags' => collect()
                ]));
            }
        }

        // Despesas - Totalizador dos Cartões de credito
        if ($budget->invoices && $budget->invoices->count()) {
            foreach ($budget->invoices as $invoice) {
                $share_value = $invoice->expenses->sum('share_value');
                $budget->expenses->push(new BudgetExpense([
                    'id' => null,
                    'description' => 'Total ' . $invoice->creditCard->name,
                    'date' => $invoice->due_date,
                    'value' => $invoice->expenses->sum('value'),
                    'remarks' => '',
                    'paid' => null,
                    'share_value' => $share_value,
                    'share_user_id' => $share_value && $shareUser ? $shareUser->id : null,
                    'tags' => collect()
                ]));

                // Receitas - Compartilhado Totalizador dos Cartões de credito
                if ($share_value > 0) {
                    $budget->incomes->push(new BudgetIncome([
                        'id' => null,
                        'description' => 'Total ' . $invoice->creditCard->name,
                        'date' => null,
                        'value' => $share_value,
                        'remarks' => 'budget.share-expense',
                        'tags' => collect()
                    ]));
                }
            }
        }

        /**
         * @todo INSERIR POSTERIOR O CARTAO PRE PAGO
         */

        if ($budgetShare) {
            // Despesas - Compartilhado com owner Totalizador dos cartoes
            if ($budgetShare && $budgetShare->expenses && $budgetShare->expenses->count() && $budgetShare->expenses->where('share_user_id', $budget->user_id)->count()) {
                $filtered = $budgetShare->expenses->filter(function ($expense) use ($budget) {
                    return $expense['share_user_id'] == $budget->user_id && $expense['id'] != null;
                });

                foreach ($filtered as $expense) {
                    $budget->expenses->push(new BudgetExpense([
                        'id' => null,
                        'description' => $expense['description'],
                        'date' => $expense['date'],
                        'value' => $expense['share_value'],
                        'remarks' => 'budget.share-expense-owner',
                        'paid' => null,
                        'share_value' => null,
                        'share_user_id' => null,
                        'tags' => collect()
                    ]));
                }
            }

            // Despesas - Compartilhado com owner Totalizador do Provisionamento
            if ($budgetShare && $budgetShare->provisions && $budgetShare->provisions->count() && $budgetShare->provisions->where('share_user_id', $budget->user_id)->count()) {
                $filtered = $budgetShare->provisions->filter(function ($provision) use ($budget) {
                    return $provision->share_user_id == $budget->user_id && $provision->id != null;
                });

                $budget->expenses->push(new BudgetExpense([
                    'id' => null,
                    'description' => 'budget.total-provision',
                    'date' => null,
                    'value' => $filtered->sum('share_value'),
                    'remarks' => 'budget.share-expense-owner',
                    'paid' => null,
                    'share_value' => null,
                    'share_user_id' => null,
                    'tags' => collect()
                ]));
            }

            // Despesas - Compartilhado com owner Totalizador dos cartoes
            if ($budgetShare->invoices && $budgetShare->invoices->count()) {
                foreach ($budgetShare->invoices as $invoice) {
                    $filtered = $invoice->expenses->filter(function ($expense) use ($budget) {
                        return $expense->share_user_id == $budget->user_id || ($expense->divisions && $expense->divisions->count() && $expense->divisions->where('share_user_id', $budget->user_id)->count());
                    });

                    if ($filtered && $filtered->count()) {
                        $budget->expenses->push(new BudgetExpense([
                            'id' => null,
                            'description' => 'Total: ' . $invoice->creditCard->name,
                            'date' => $invoice->due_date,
                            'value' => $filtered->sum('share_value'),
                            'remarks' => 'budget.share-expense-owner',
                            'paid' => null,
                            'share_value' => null,
                            'share_user_id' => null,
                            'tags' => collect()
                        ]));
                    }
                }
            }
        }
    }

    /**
     * @param Budget $budget
     * @param Budget|null $budgetShare
     * @return array
     */
    private function mountResume(Budget $budget, Budget $budgetShare = null): array
    {
        $balance = $budget->total_income - $budget->total_expense;
        $pay_share = 0;
        $receive_share = 0;
        $balance_share = 0;

        if ($budget->expenses && $budget->expenses->count()) {
            $receive_share += $budget->expenses->sum('share_value');
        }

        if ($budget->provisions && $budget->provisions->count()) {
            $receive_share += $budget->provisions->sum('share_value');
        }

        if ($budget->invoices && $budget->invoices->count()) {
            foreach ($budget->invoices as $invoice) {
                if ($invoice->expenses && $invoice->expenses->count()) {
                    $receive_share += $invoice->expenses->sum('share_value');
                }
            }
        }

        if ($budgetShare) {
            if ($budgetShare->expenses && $budgetShare->expenses->count()) {
                $pay_share += $budgetShare->expenses->sum('share_value');
            }

            if ($budgetShare->provisions && $budgetShare->provisions->count()) {
                $pay_share += $budgetShare->provisions->sum('share_value');
            }

            if ($budgetShare->invoices && $budgetShare->invoices->count()) {
                foreach ($budgetShare->invoices as $invoice) {
                    if ($invoice->expenses && $invoice->expenses->count()) {
                        $pay_share += $invoice->expenses->sum('share_value');
                    }
                }
            }
        }

        $balance_share = $receive_share - $pay_share;

        // Dados para o resumo do cartão e Provisionamento
        $resume_credit_card = collect();
        $total_value = 0;
        $total_share_value = 0;

        // Total do parcelamento
        if ($budget->invoices && $budget->invoices->count()) {
            foreach ($budget->invoices as $invoice) {
                if ($invoice->expenses && $invoice->expenses->count()) {
                    $filtered = $invoice->expenses->where('group', '=', 'PORTION');

                    if ($filtered && $filtered->count()) {
                        $total_value += $filtered->sum('value');
                        $total_share_value += $filtered->sum('share_value');
                    }
                }
            }
        }

        $resume_credit_card->push([
            'name' => 'budget-resume.total-portion',
            'total_value' => $total_value,
            'total_share_value' => $total_share_value,
        ]);

        $total_value = 0;
        $total_share_value = 0;

        // Total Provisionamento
        if ($budget->provisions && $budget->provisions->count()) {
            $total_value = $budget->provisions->sum('value');
            $total_share_value = $budget->provisions->sum('share_value');
        }

        $resume_credit_card->push([
            'name' => 'budget-resume.total-provisions',
            'total_value' => $total_value,
            'total_share_value' => $total_share_value,
        ]);

        // Total das semanas
        $total_value = 0;
        $total_share_value = 0;
        // Semana 1
        foreach ($budget->invoices as $invoice) {
            if ($invoice->expenses && $invoice->expenses->count()) {
                $filtered = $invoice->expenses->where('group', '=', 'WEEK_1');

                if ($filtered && $filtered->count()) {
                    $total_value += $filtered->sum('value');
                    $total_share_value += $filtered->sum('share_value');
                }
            }
        }

        $resume_credit_card->push([
            'name' => 'budget-resume.total-week-1',
            'total_value' => $total_value,
            'total_share_value' => $total_share_value,
        ]);


        $total_value = 0;
        $total_share_value = 0;
        // Semana 2
        foreach ($budget->invoices as $invoice) {
            if ($invoice->expenses && $invoice->expenses->count()) {
                $filtered = $invoice->expenses->where('group', '=', 'WEEK_2');

                if ($filtered && $filtered->count()) {
                    $total_value += $filtered->sum('value');
                    $total_share_value += $filtered->sum('share_value');
                }
            }
        }

        $resume_credit_card->push([
            'name' => 'budget-resume.total-week-2',
            'total_value' => $total_value,
            'total_share_value' => $total_share_value,
        ]);

        $total_value = 0;
        $total_share_value = 0;

        // Semana 3
        foreach ($budget->invoices as $invoice) {
            if ($invoice->expenses && $invoice->expenses->count()) {
                $filtered = $invoice->expenses->where('group', '=', 'WEEK_3');

                if ($filtered && $filtered->count()) {
                    $total_value += $filtered->sum('value');
                    $total_share_value += $filtered->sum('share_value');
                }
            }
        }

        $resume_credit_card->push([
            'name' => 'budget-resume.total-week-3',
            'total_value' => $total_value,
            'total_share_value' => $total_share_value,
        ]);

        $total_value = 0;
        $total_share_value = 0;

        // Semana 4
        foreach ($budget->invoices as $invoice) {
            if ($invoice->expenses && $invoice->expenses->count()) {
                $filtered = $invoice->expenses->where('group', '=', 'WEEK_4');

                if ($filtered && $filtered->count()) {
                    $total_value += $filtered->sum('value');
                    $total_share_value += $filtered->sum('share_value');
                }
            }
        }

        $resume_credit_card->push([
            'name' => 'budget-resume.total-week-4',
            'total_value' => $total_value,
            'total_share_value' => $total_share_value,
        ]);

        // Total a vista
        $filtered_cash = $resume_credit_card->slice(1);
        $resume_credit_card->push([
            'name' => 'budget-resume.total-cash',
            'total_value' => collect($filtered_cash)->sum('total_value'),
            'total_share_value' => collect($filtered_cash)->sum('total_share_value'),
        ]);

        // Total Geral
        $resume_credit_card->push([
            'name' => 'default.total',
            'total_value' => $resume_credit_card->sum('total_value'),
            'total_share_value' => $resume_credit_card->sum('total_share_value'),
        ]);

        return [
            'total_expense' => $budget->total_expense,
            'total_income' => $budget->total_income,
            'balance' => $balance,
            'pay_share' => $pay_share,
            'receive_share' => $receive_share,
            'balance_share' => $balance_share,
            'resume_credit_card' => $resume_credit_card,
        ];
    }

    /**
     * Undocumented function
     *
     * @param Budget $budget
     * @param Budget|null $budgetShare
     * @return array
     */
    private function mountGoalsChart(Budget $budget, Budget $budgetShare = null): array
    {
        $goals_charts = collect();
        $total_expense = 0;

        if ($budget->goals && $budget->goals->count()) {
            foreach ($budget->goals as $goal) {
                $total_expense = 0;
                foreach ($goal->tags as $tag) {
                    // Procuro em Provisionamento
                    if ($budget->provisions && $budget->provisions->count()) {
                        foreach ($budget->provisions as $provision) {
                            if ($provision->tags && $provision->tags->count() && $provision->tags->contains('name', $tag->name) && (!$goal->group || $provision->group === $goal->group)) {
                                $total_expense += $provision->value;
                            }
                        }
                    }

                    // Procuro em Despesas
                    if ($budget->expenses && $budget->expenses->count()) {
                        foreach ($budget->expenses as $expense) {
                            if ($expense->tags && $expense->tags->count() && $expense->tags->contains('name', $tag->name) && (!$goal->group || $goal->group === 'MONTHLY')) {
                                $total_expense += $expense->value;
                            }
                        }
                    }

                    // Procuro em Cartoes de Credito
                    if ($budget->invoices && $budget->invoices->count()) {
                        foreach ($budget->invoices as $invoice) {
                            if ($invoice->expenses && $invoice->expenses->count()) {
                                foreach ($invoice->expenses as $expense) {
                                    if (!$goal->group || $expense->group === $goal->group) {
                                        if ($expense->divisions && $expense->divisions->count()) {
                                            foreach ($expense->divisions as $division) {
                                                if ($division->tags && $division->tags->count() && $division->tags->contains('name', $tag->name)) {
                                                    $total_expense += $division->value;
                                                }
                                            }
                                        } else if ($expense->tags && $expense->tags->count() && $expense->tags->contains('name', $tag->name)) {
                                            $total_expense += $expense->value;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($goal->count_share && $budgetShare) {
                        // Procuro em Provisionamento
                        if ($budgetShare->provisions && $budgetShare->provisions->count()) {
                            foreach ($budgetShare->provisions as $provision) {
                                if ($provision->tags && $provision->tags->count() && $provision->tags->contains('name', $tag->name) && (!$goal->group || $provision->group === 'MONTHLY')) {
                                    $total_expense += $provision->value;
                                }
                            }
                        }

                        // Procuro em Despesas
                        if ($budgetShare->expenses && $budgetShare->expenses->count()) {
                            foreach ($budgetShare->expenses as $expense) {
                                if ($expense->tags && $expense->tags->count() && $expense->tags->contains('name', $tag->name) && (!$goal->group || $goal->group === 'MONTHLY')) {
                                    $total_expense += $expense->value;
                                }
                            }
                        }

                        // Procuro em Cartoes de Credito
                        if ($budgetShare->invoices && $budgetShare->invoices->count()) {
                            foreach ($budgetShare->invoices as $invoice) {
                                if ($invoice->expenses && $invoice->expenses->count()) {
                                    foreach ($invoice->expenses as $expense) {
                                        if (!$goal->group || $expense->group === $goal->group) {
                                            if ($expense->divisions && $expense->divisions->count()) {
                                                foreach ($expense->divisions as $division) {
                                                    if ($division->tags && $division->tags->count() && $division->tags->contains('name', $tag->name)) {
                                                        $total_expense += $division->value;
                                                    }
                                                }
                                            } else if ($expense->tags && $expense->tags->count() && $expense->tags->contains('name', $tag->name)) {
                                                $total_expense += $expense->value;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $goals_charts->push([
                        'tag' => $tag->name,
                        'description' => $goal->description,
                        'value' => $goal->value,
                        'total' => $total_expense
                    ]);
                }
            }
        }

        return $goals_charts->toArray();
    }

    /**
     * @param Budget $budget
     * @param Budget|null $budgetShare
     * @return array
     */
    private function mountExpenseToTags(Budget $budget): array
    {
        $expense_to_tags = [];
        $return_data = collect();
        $sorted = [];

        // Procuro em Provisionamento
        if ($budget->provisions && $budget->provisions->count()) {
            foreach ($budget->provisions as $provision) {
                if ($provision->tags && $provision->tags->count()) {
                    foreach ($provision->tags as $tag) {
                        if (!isset($expense_to_tags[$tag->name])) {
                            $expense_to_tags[$tag->name] = 0;
                        }

                        $expense_to_tags[$tag->name] += $provision->value;
                    }
                }
            }
        }

        // Procuro em Despesas
        if ($budget->expenses && $budget->expenses->count()) {
            foreach ($budget->expenses as $expense) {
                if ($expense->tags && $expense->tags->count()) {
                    foreach ($expense->tags as $tag) {
                        if (!isset($expense_to_tags[$tag->name])) {
                            $expense_to_tags[$tag->name] = 0;
                        }

                        $expense_to_tags[$tag->name] += $expense->value;
                    }
                }
            }
        }

        // Procuro em Cartoes de Credito
        if ($budget->invoices && $budget->invoices->count()) {
            foreach ($budget->invoices as $invoice) {
                if ($invoice->expenses && $invoice->expenses->count()) {
                    foreach ($invoice->expenses as $expense) {
                        if ($expense->divisions && $expense->divisions->count()) {
                            foreach ($expense->divisions as $division) {
                                if ($division->tags && $division->tags->count()) {
                                    foreach ($division->tags as $tag) {
                                        if (!isset($expense_to_tags[$tag->name])) {
                                            $expense_to_tags[$tag->name] = 0;
                                        }

                                        $expense_to_tags[$tag->name] += $division->value;
                                    }
                                }
                            }
                        } else if ($expense->tags && $expense->tags->count()) {
                            foreach ($expense->tags as $tag) {
                                if (!isset($expense_to_tags[$tag->name])) {
                                    $expense_to_tags[$tag->name] = 0;
                                }

                                $expense_to_tags[$tag->name] += $expense->value;
                            }
                        }
                    }
                }
            }
        }

        if (count($expense_to_tags) > 0) {
            collect($expense_to_tags)->each(function (float $value, string $key) use ($return_data) {
                $return_data->push([
                    'tag' => $key,
                    'value' => $value,
                ]);
            });

            $sorted = $return_data->sortBy('value');
        }

        return count($sorted) ? $sorted->values()->all() : $sorted;
    }
}
