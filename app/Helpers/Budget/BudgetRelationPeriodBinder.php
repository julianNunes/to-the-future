<?php

namespace App\Helpers\Budget;

use App\Helpers\Budget\Interfaces\BudgetRelationPeriodBinderInterface;
use App\Models\Budget;
use App\Repositories\Interfaces\CreditCardInvoiceRepositoryInterface;
use App\Repositories\Interfaces\PrepaidCardExtractRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class BudgetRelationPeriodBinder implements BudgetRelationPeriodBinderInterface
{
    public function __construct(
        private CreditCardInvoiceRepositoryInterface $creditCardInvoiceRepository,
        private PrepaidCardExtractRepositoryInterface $prepaidCardExtractRepository,
    ) {}

    public function bindUnlinkedRelations(Budget $budget): void
    {
        $this->bindCreditCardInvoices($budget);
        $this->bindPrepaidCardExtracts($budget);
    }

    private function bindCreditCardInvoices(Budget $budget): void
    {
        $creditCardInvoices = $this->creditCardInvoiceRepository->get(
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

        foreach ($creditCardInvoices as $invoice) {
            $this->creditCardInvoiceRepository->store(['budget_id' => $budget->id], $invoice);
        }
    }

    private function bindPrepaidCardExtracts(Budget $budget): void
    {
        $prepaidCardExtracts = $this->prepaidCardExtractRepository->get(
            function (Builder $query) use ($budget) {
                $query
                    ->where(['year' => $budget->year, 'month' => $budget->month, 'budget_id' => null])
                    ->whereHas('prepaidCard', function (Builder $query) use ($budget) {
                        $query->where('user_id', $budget->user_id)->where('is_active', true);
                    });
            },
            [],
            [],
            []
        );

        foreach ($prepaidCardExtracts as $extract) {
            $this->prepaidCardExtractRepository->store(['budget_id' => $budget->id], $extract);
        }
    }
}
