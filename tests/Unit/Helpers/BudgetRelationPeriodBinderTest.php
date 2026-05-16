<?php

namespace Tests\Unit\Helpers;

use App\Helpers\Budget\BudgetRelationPeriodBinder;
use App\Models\Budget;
use App\Models\CreditCardInvoice;
use App\Models\PrepaidCardExtract;
use App\Repositories\Interfaces\CreditCardInvoiceRepositoryInterface;
use App\Repositories\Interfaces\PrepaidCardExtractRepositoryInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class BudgetRelationPeriodBinderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testItBindsUnlinkedInvoicesAndExtractsForBudgetPeriod(): void
    {
        $budget = new Budget([
            'year' => '2026',
            'month' => '05',
            'user_id' => 7,
        ]);
        $budget->id = 11;

        $invoice = new CreditCardInvoice();
        $extract = new PrepaidCardExtract();

        $creditCardInvoiceRepository = Mockery::mock(CreditCardInvoiceRepositoryInterface::class);
        $prepaidCardExtractRepository = Mockery::mock(PrepaidCardExtractRepositoryInterface::class);

        $creditCardInvoiceRepository->shouldReceive('get')
            ->once()
            ->with(Mockery::type('Closure'), [], [], [])
            ->andReturn(collect([$invoice]));

        $creditCardInvoiceRepository->shouldReceive('store')
            ->once()
            ->with(['budget_id' => 11], $invoice)
            ->andReturn($invoice);

        $prepaidCardExtractRepository->shouldReceive('get')
            ->once()
            ->with(Mockery::type('Closure'), [], [], [])
            ->andReturn(collect([$extract]));

        $prepaidCardExtractRepository->shouldReceive('store')
            ->once()
            ->with(['budget_id' => 11], $extract)
            ->andReturn($extract);

        $helper = new BudgetRelationPeriodBinder($creditCardInvoiceRepository, $prepaidCardExtractRepository);

        $helper->bindUnlinkedRelations($budget);
    }
}
