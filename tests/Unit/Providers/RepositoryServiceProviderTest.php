<?php

namespace Tests\Unit\Providers;

use App\Repositories\BudgetExpenseTagOptionRepository;
use App\Repositories\Interfaces\BudgetExpenseTagOptionRepositoryInterface;
use Tests\TestCase;

class RepositoryServiceProviderTest extends TestCase
{
    public function testBudgetExpenseTagOptionRepositoryInterfaceIsBound(): void
    {
        $repository = $this->app->make(BudgetExpenseTagOptionRepositoryInterface::class);

        $this->assertInstanceOf(BudgetExpenseTagOptionRepository::class, $repository);
    }
}
