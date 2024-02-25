<?php

namespace App\Helpers\Budget;

use App\Helpers\Budget\Interfaces\BudgetShowDataInterface;
use App\Repositories\Interfaces\{
    BudgetRepositoryInterface,
    CreditCardInvoiceRepositoryInterface,
    FinancingInstallmentRepositoryInterface,
    ShareUserRepositoryInterface,
};

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

    public function dataShow(int $id)
    {

        // 'owner' => [
        //     'installments'
        //     'goalsCharts',
        //     'budget',
        //     'expenseToTags',
        //     'summary'
        // ]

        // ],
        // 'share' => [
        //     'installments'
        //     'goalsCharts',
        //     'budget',
        //     'expenseToTags',
        //     'summary'
        // ]
    }

    private function mountExpensesAndIncomes()
    {
    }
}
