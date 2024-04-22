<?php

namespace App\Providers;

use App\Services\Interfaces\{
    BudgetExpenseServiceInterface,
    BudgetGoalServiceInterface,
    BudgetIncomeServiceInterface,
    BudgetProvisionServiceInterface,
    BudgetServiceInterface,
    CreditCardInvoiceExpenseServiceInterface,
    CreditCardInvoiceServiceInterface,
    CreditCardServiceInterface,
    FinancingInstallmentServiceInterface,
    FinancingServiceInterface,
    FixExpenseServiceInterface,
    PrepaidCardExtractExpenseServiceInterface,
    PrepaidCardExtractServiceInterface,
    PrepaidCardServiceInterface,
    ProvisionServiceInterface,
    TagServiceInterface
};
use App\Services\{
    BudgetExpenseService,
    BudgetGoalService,
    BudgetIncomeService,
    BudgetProvisionService,
    BudgetService,
    CreditCardInvoiceExpenseService,
    CreditCardInvoiceService,
    CreditCardService,
    FinancingInstallmentService,
    FinancingService,
    FixExpenseService,
    PrepaidCardExtractExpenseService,
    PrepaidCardExtractService,
    PrepaidCardService,
    ProvisionService,
    TagService
};
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // if ($this->app->isLocal()) {
        //     $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        // }
        // $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);

        // Apps Services
        $this->app->bind(BudgetExpenseServiceInterface::class, BudgetExpenseService::class);
        $this->app->bind(BudgetGoalServiceInterface::class, BudgetGoalService::class);
        $this->app->bind(BudgetIncomeServiceInterface::class, BudgetIncomeService::class);
        $this->app->bind(BudgetProvisionServiceInterface::class, BudgetProvisionService::class);
        $this->app->bind(BudgetServiceInterface::class, BudgetService::class);
        $this->app->bind(CreditCardInvoiceServiceInterface::class, CreditCardInvoiceService::class);
        $this->app->bind(CreditCardInvoiceExpenseServiceInterface::class, CreditCardInvoiceExpenseService::class);
        $this->app->bind(CreditCardServiceInterface::class, CreditCardService::class);
        $this->app->bind(PrepaidCardExtractExpenseServiceInterface::class, PrepaidCardExtractExpenseService::class);
        $this->app->bind(PrepaidCardExtractServiceInterface::class, PrepaidCardExtractService::class);
        $this->app->bind(PrepaidCardServiceInterface::class, PrepaidCardService::class);
        $this->app->bind(FinancingServiceInterface::class, FinancingService::class);
        $this->app->bind(FinancingInstallmentServiceInterface::class, FinancingInstallmentService::class);
        $this->app->bind(FixExpenseServiceInterface::class, FixExpenseService::class);
        $this->app->bind(ProvisionServiceInterface::class, ProvisionService::class);
        $this->app->bind(TagServiceInterface::class, TagService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
