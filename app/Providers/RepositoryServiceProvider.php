<?php

namespace App\Providers;

use App\Repositories\{
    AppRepository,
    BudgetExpenseRepository,
    BudgetGoalRepository,
    BudgetIncomeRepository,
    BudgetProvisionRepository,
    BudgetRepository,
    CreditCardInvoiceExpenseDivisionRepository,
    CreditCardInvoiceExpenseRepository,
    CreditCardInvoiceFileRepository,
    CreditCardInvoiceRepository,
    CreditCardRepository,
    FinancingInstallmentRepository,
    FinancingRepository,
    FixExpenseRepository,
    PrepaidCardExtractExpenseRepository,
    PrepaidCardExtractRepository,
    PrepaidCardRepository,
    ProvisionRepository,
    ShareUserRepository,
    TagRepository
};
use App\Repositories\Interfaces\{
    AppRepositoryInterface,
    BudgetExpenseRepositoryInterface,
    BudgetGoalRepositoryInterface,
    BudgetIncomeRepositoryInterface,
    BudgetProvisionRepositoryInterface,
    BudgetRepositoryInterface,
    CreditCardInvoiceExpenseDivisionRepositoryInterface,
    CreditCardInvoiceExpenseRepositoryInterface,
    CreditCardInvoiceFileRepositoryInterface,
    CreditCardInvoiceRepositoryInterface,
    CreditCardRepositoryInterface,
    FinancingInstallmentRepositoryInterface,
    FinancingRepositoryInterface,
    FixExpenseRepositoryInterface,
    PrepaidCardExtractExpenseRepositoryInterface,
    PrepaidCardExtractRepositoryInterface,
    PrepaidCardRepositoryInterface,
    ProvisionRepositoryInterface,
    ShareUserRepositoryInterface,
    TagRepositoryInterface
};
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AppRepositoryInterface::class, AppRepository::class);
        $this->app->bind(BudgetExpenseRepositoryInterface::class, BudgetExpenseRepository::class);
        $this->app->bind(BudgetGoalRepositoryInterface::class, BudgetGoalRepository::class);
        $this->app->bind(BudgetIncomeRepositoryInterface::class, BudgetIncomeRepository::class);
        $this->app->bind(BudgetProvisionRepositoryInterface::class, BudgetProvisionRepository::class);
        $this->app->bind(BudgetRepositoryInterface::class, BudgetRepository::class);
        $this->app->bind(CreditCardInvoiceExpenseDivisionRepositoryInterface::class, CreditCardInvoiceExpenseDivisionRepository::class);
        $this->app->bind(CreditCardInvoiceExpenseRepositoryInterface::class, CreditCardInvoiceExpenseRepository::class);
        $this->app->bind(CreditCardInvoiceFileRepositoryInterface::class, CreditCardInvoiceFileRepository::class);
        $this->app->bind(CreditCardInvoiceRepositoryInterface::class, CreditCardInvoiceRepository::class);
        $this->app->bind(CreditCardRepositoryInterface::class, CreditCardRepository::class);
        $this->app->bind(PrepaidCardRepositoryInterface::class, PrepaidCardRepository::class);
        $this->app->bind(PrepaidCardExtractRepositoryInterface::class, PrepaidCardExtractRepository::class);
        $this->app->bind(PrepaidCardExtractExpenseRepositoryInterface::class, PrepaidCardExtractExpenseRepository::class);
        $this->app->bind(FinancingInstallmentRepositoryInterface::class, FinancingInstallmentRepository::class);
        $this->app->bind(FinancingRepositoryInterface::class, FinancingRepository::class);
        $this->app->bind(FixExpenseRepositoryInterface::class, FixExpenseRepository::class);
        $this->app->bind(ProvisionRepositoryInterface::class, ProvisionRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
        $this->app->bind(ShareUserRepositoryInterface::class, ShareUserRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
