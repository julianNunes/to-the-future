<?php

namespace App\Helpers\Budget;

use App\Helpers\Budget\Interfaces\BudgetShowDataInterface;
use App\Models\Budget;
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

        $this->mounteExpensesIncomes($budget, $budgetShare, $shareUser->shareUser);

        if ($shareUser && $budgetShare) {
            $this->mounteExpensesIncomes($budgetShare, $budget, $shareUser->user);
        }

        /**
         * @todo Montar as Metas e dados para o grafico
         */

        $goals_charts = [];

        // - Componente:
        //     - Painel com as metas (COLUNA 12)
        //         - De um lado tabela com dados
        //         - Do Outro grafico com o utilizado e a meta

        //     - Painel "Despesas por Tags"
        //         - De um lado tabela com dados
        //         - Do Outro grafico com o utilizado e a meta

        //     - Painel de Resumo de pagamento/recebimento do usuario compartilhado  (COLUNA 6)
        //         - Somar o valor que devo pagar para o "share_user_id"
        //         - Somar o valor a receber do "share_user_id"
        //         - BALANÇO entre RECEITAS E DESPESAS

        //     - Painel de Resumo dos Cartões (COLUNA 6)
        //         - Parcelados
        //         - Provisionamento
        //         - Semana 1, 2, 3, 4

        return [
            'installments' => $installments,
            'shareUser' => $shareUser,
            'shareUsers' => $shareUsers,
            'owner' => [
                'budget' => $budget,
                'summary' => [],
                'summaryCrediCard' => [],
                'goalsCharts' => [],
                'expenseToTags' => [],
            ],
            'share' => [
                'budget' => $budgetShare,
                'summary' => [],
                'summaryCrediCard' => [],
                'goalsCharts' => [],
                'expenseToTags' => [],
            ]
        ];
    }

    /**
     * @param Budget $budget
     * @param Budget|null $budgetShare
     * @param User|null $shareUser
     * @return void
     */
    private function mounteExpensesIncomes(Budget &$budget, Budget &$budgetShare = null, User $shareUser = null)
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
            // $filtered = $budget->expenses->filter(function ($expense) use ($budget) {
            //     return $expense['share_user_id'] != null && $expense['id'] == null;
            // });

            if ($filtered && $filtered->count()) {
                foreach ($filtered as $expense) {
                    $budget->incomes->push([
                        'id' => null,
                        'description' => $expense->description,
                        'date' => null,
                        'value' => $expense->share_value,
                        'remarks' => 'budget.share-expense',
                        'tags' => []
                    ]);
                }
            }
        }

        // Despesas - Totalizador do Provisionamento
        if ($budget->provisions && $budget->provisions->count()) {
            $share_value = $budget->provisions->sum('share_value');
            $budget->expenses->push([
                'id' => null,
                'description' => 'budget.total-provision',
                'date' => null,
                'value' => $budget->provisions->sum('value'),
                'remarks' => '',
                'paid' => null,
                'share_value' => $share_value,
                'share_user_id' => $share_value && $shareUser ? $shareUser->id : null,
                'tags' => []
            ]);

            // Receitas - Compartilhado Totalizador do Provisionamento
            if ($share_value > 0) {
                $budget->incomes->push([
                    'id' => null,
                    'description' => 'budget.total-provision',
                    'date' => null,
                    'value' => $share_value,
                    'remarks' => 'budget.share-expense',
                    'tags' => []
                ]);
            }
        }

        // Despesas - Totalizador dos Cartões de credito
        if ($budget->invoices && $budget->invoices->count()) {
            foreach ($budget->invoices as $invoice) {
                $share_value = $invoice->expenses->sum('share_value');
                $budget->expenses->push([
                    'id' => null,
                    'description' => 'Total ' . $invoice->creditCard->name,
                    'date' => $invoice->due_date,
                    'value' => $invoice->expenses->sum('value'),
                    'remarks' => '',
                    'paid' => null,
                    'share_value' => $share_value,
                    'share_user_id' => $share_value && $shareUser ? $shareUser->id : null,
                    'tags' => []
                ]);

                // Receitas - Compartilhado Totalizador dos Cartões de credito
                if ($share_value > 0) {
                    $budget->incomes->push([
                        'id' => null,
                        'description' => 'Total ' . $invoice->creditCard->name,
                        'date' => null,
                        'value' => $share_value,
                        'remarks' => 'budget.share-expense',
                        'tags' => []
                    ]);
                }
            }
        }

        /**
         * @todo INSERIR POSTERIOR O CARTAO PRE PAGO
         */

        // Despesas - Compartilhado com owner Totalizador dos cartoes
        if ($budgetShare && $budgetShare->expenses && $budgetShare->expenses->count() && $budgetShare->expenses->where('share_user_id', $budget->user_id)->count()) {
            $filtered = $budgetShare->expenses->filter(function ($expense) use ($budget) {
                return $expense['share_user_id'] == $budget->user_id && $expense['id'] != null;
            });

            foreach ($filtered as $expense) {
                $budget->expenses->push([
                    'id' => null,
                    'description' => $expense['description'],
                    'date' => $expense['date'],
                    'value' => $expense['share_value'],
                    'remarks' => 'budget.share-expense-owner',
                    'paid' => null,
                    'share_value' => null,
                    'share_user_id' => null,
                    'tags' => []
                ]);
            }
        }

        // Despesas - Compartilhado com owner Totalizador do Provisionamento
        if ($budgetShare && $budgetShare->provisions && $budgetShare->provisions->count() && $budgetShare->provisions->where('share_user_id', $budget->user_id)->count()) {
            $filtered = $budgetShare->provisions->filter(function ($provision) use ($budget) {
                return $provision->share_user_id == $budget->user_id && $provision->id != null;
            });

            $budget->expenses->push([
                'id' => null,
                'description' => 'budget.total-provision',
                'date' => null,
                'value' => $filtered->sum('share_value'),
                'remarks' => 'budget.share-expense-owner',
                'paid' => null,
                'share_value' => null,
                'share_user_id' => null,
                'tags' => []
            ]);
        }

        // Despesas - Compartilhado com owner Totalizador dos cartoes
        if ($budgetShare->invoices && $budgetShare->invoices->count()) {
            foreach ($budgetShare->invoices as $invoice) {
                $filtered = $invoice->expenses->filter(function ($expense) use ($budget) {
                    return $expense->share_user_id == $budget->user_id || ($expense->divisions && $expense->divisions->count() && $expense->divisions->where('share_user_id', $budget->user_id)->count());
                });

                if ($filtered && $filtered->count()) {
                    $budget->expenses->push([
                        'id' => null,
                        'description' => 'Total: ' . $invoice->creditCard->name,
                        'date' => $invoice->due_date,
                        'value' => $filtered->sum('share_value'),
                        'remarks' => 'budget.share-expense-owner',
                        'paid' => null,
                        'share_value' => null,
                        'share_user_id' => null,
                        'tags' => []
                    ]);
                }
            }
        }
    }
}
