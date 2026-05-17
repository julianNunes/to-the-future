<?php

namespace Tests\Unit\Services;

use App\Helpers\Budget\Interfaces\BudgetCalculateInterface;
use App\Models\Budget;
use App\Models\BudgetExpense;
use App\Models\User;
use App\Repositories\Interfaces\BudgetExpenseRepositoryInterface;
use App\Repositories\Interfaces\BudgetRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Services\BudgetExpenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class BudgetExpenseServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use RefreshDatabase;

    public function testDeleteAllPortionsRecalculatesEachBudgetOnlyOncePerShareMode(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $budgetRepository = Mockery::mock(BudgetRepositoryInterface::class);
        $budgetExpenseRepository = Mockery::mock(BudgetExpenseRepositoryInterface::class);
        $tagRepository = Mockery::mock(TagRepositoryInterface::class);
        $budgetCalculate = Mockery::mock(BudgetCalculateInterface::class);

        $budget = new Budget(['user_id' => $user->id]);
        $budget->id = 10;

        $expenseA = new BudgetExpense(['budget_id' => 10, 'share_user_id' => null]);
        $expenseA->id = 1;
        $expenseB = new BudgetExpense(['budget_id' => 10, 'share_user_id' => 9]);
        $expenseB->id = 2;
        $expenseC = new BudgetExpense(['budget_id' => 10, 'share_user_id' => 9]);
        $expenseC->id = 3;

        $budgetExpenseRepository->shouldReceive('get')
            ->once()
            ->with(['group_portion' => 'GROUP-1'])
            ->andReturn(collect([$expenseA, $expenseB, $expenseC]));

        $budgetRepository->shouldReceive('show')
            ->times(3)
            ->with(10)
            ->andReturn($budget);

        $tagRepository->shouldReceive('saveTagsToModel')
            ->times(3);

        $budgetExpenseRepository->shouldReceive('delete')
            ->once()
            ->with(1)
            ->andReturn(true);

        $budgetExpenseRepository->shouldReceive('delete')
            ->once()
            ->with(2)
            ->andReturn(true);

        $budgetExpenseRepository->shouldReceive('delete')
            ->once()
            ->with(3)
            ->andReturn(true);

        $budgetCalculate->shouldReceive('recalculate')
            ->once()
            ->with(10, false);

        $budgetCalculate->shouldReceive('recalculate')
            ->once()
            ->with(10, true);

        $service = new BudgetExpenseService(
            $budgetRepository,
            $budgetExpenseRepository,
            $tagRepository,
            $budgetCalculate,
        );

        $this->assertTrue($service->deleteAllPortions('GROUP-1'));
    }
}
