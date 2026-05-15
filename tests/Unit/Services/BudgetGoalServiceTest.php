<?php

namespace Tests\Unit\Services;

use App\Repositories\Interfaces\BudgetGoalRepositoryInterface;
use App\Repositories\Interfaces\BudgetRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Services\BudgetGoalService;
use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class BudgetGoalServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testDeleteThrowsWhenGoalDoesNotExist(): void
    {
        $budgetRepository = Mockery::mock(BudgetRepositoryInterface::class);
        $goalRepository = Mockery::mock(BudgetGoalRepositoryInterface::class);
        $tagRepository = Mockery::mock(TagRepositoryInterface::class);

        $goalRepository->shouldReceive('show')->once()->with(10)->andReturn(null);
        $tagRepository->shouldNotReceive('saveTagsToModel');
        $goalRepository->shouldNotReceive('delete');

        $service = new BudgetGoalService($budgetRepository, $goalRepository, $tagRepository);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('budget-goal.not-found');

        $service->delete(10);
    }
}