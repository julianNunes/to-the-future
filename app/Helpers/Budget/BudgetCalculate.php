<?php

namespace App\Helpers\Budget;

use App\Helpers\Budget\Interfaces\BudgetCalculateInterface;
use App\Repositories\Interfaces\{
    BudgetRepositoryInterface,
    CreditCardInvoiceRepositoryInterface,
    FinancingInstallmentRepositoryInterface,
    ShareUserRepositoryInterface,
};
use Exception;
use Illuminate\Database\Eloquent\Builder;

class BudgetCalculate implements BudgetCalculateInterface
{
    public function __construct(
        // Repositories
        private BudgetRepositoryInterface $budgetRepository,
        private ShareUserRepositoryInterface $shareUserRepository,
        private CreditCardInvoiceRepositoryInterface $creditCardInvoiceRepository,
        private FinancingInstallmentRepositoryInterface $financingInstallmentRepository,
    ) {
    }

    /**
     * Recalculate budget
     * @param integer $id
     * @param boolean $calculateShareUser
     * @return boolean
     */
    public function recalculate(int $id, bool $calculateShareUser = false): bool
    {
        // Busca o budget do id
        // com despesas, receitas e provisionamento
        $budget = $this->budgetRepository->show($id, [
            'expenses',
            'incomes',
            'provisions',
            'invoices.expenses'
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

        if ($budget->invoices && $budget->invoices->count()) {
            foreach ($budget->invoices as $invoice) {
                if ($invoice->expenses && $invoice->expenses->count()) {
                    $total_expense += $invoice->expenses->sum('value');
                    $total_income += $invoice->expenses->sum('share_value');
                }
            }
        }

        // busca share user
        $share_user = $this->shareUserRepository->getOne(['user_id' => $budget->user_id], [], [], ['shareUser']);
        $budget_share = null;

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
                                })
                                ->orWhereHas('invoices.expenses', function (Builder $query) use ($budget) {
                                    $query->where('share_user_id', $budget->user_id)
                                        ->orWhereHas('divisons', function (Builder $query2) use ($budget) {
                                            $query2->where('share_user_id', $budget->user_id);
                                        });
                                });
                        });
                },
                [],
                [],
                [
                    'expenses' => function (Builder $query) use ($budget) {
                        $query->where('share_user_id', $budget->user_id);
                    },
                    'provisions' => function (Builder $query) use ($budget) {
                        $query->where('share_user_id', $budget->user_id);
                    },
                    'invoices.expenses' => function (Builder $query) use ($budget) {
                        $query->where('share_user_id', $budget->user_id)
                            ->orWhereHas('divisons', function (Builder $query2) use ($budget) {
                                $query2->where('share_user_id', $budget->user_id);
                            });
                    },
                ]
            );

            if ($budget_share) {
                // Soma Despesas
                if ($budget_share->expenses && $budget_share->expenses->count()) {
                    $total_expense += $budget_share->expenses->sum('share_value');
                }

                // Soma Provisionamento
                if ($budget_share->provisions && $budget_share->provisions->count()) {
                    $total_expense += $budget_share->provisions->sum('share_value');
                }

                // Soma Faturas

                if ($budget_share->invoices && $budget_share->invoices->count()) {
                    foreach ($budget_share->invoices as $invoice) {
                        if ($invoice->expenses && $invoice->expenses->count()) {
                            $total_expense += $invoice->expenses->sum('share_value');
                        }
                    }
                }
            }
        }

        return $this->budgetRepository->store([
            'total_expense' => $total_expense,
            'total_income' => $total_income
        ], $budget);

        if ($calculateShareUser && $budget_share) {
            $this->recalculate($budget_share->id);
        }

        return true;
    }
}
