<?php

namespace Tests\Unit\Services;

use App\Helpers\Budget\Interfaces\BudgetCalculateInterface;
use App\Helpers\Budget\Interfaces\BudgetRelationPeriodBinderInterface;
use App\Helpers\Budget\Interfaces\BudgetShowDataInterface;
use App\Models\Budget;
use App\Models\BudgetIncome;
use App\Models\User;
use App\Repositories\Interfaces\BudgetExpenseRepositoryInterface;
use App\Repositories\Interfaces\BudgetGoalRepositoryInterface;
use App\Repositories\Interfaces\BudgetIncomeRepositoryInterface;
use App\Repositories\Interfaces\BudgetProvisionRepositoryInterface;
use App\Repositories\Interfaces\BudgetRepositoryInterface;
use App\Repositories\Interfaces\CreditCardInvoiceRepositoryInterface;
use App\Repositories\Interfaces\FinancingInstallmentRepositoryInterface;
use App\Repositories\Interfaces\FixExpenseRepositoryInterface;
use App\Repositories\Interfaces\PrepaidCardExtractRepositoryInterface;
use App\Repositories\Interfaces\ProvisionRepositoryInterface;
use App\Services\BudgetService;
use App\Services\Interfaces\BudgetExpenseServiceInterface;
use App\Services\Interfaces\BudgetGoalServiceInterface;
use App\Services\Interfaces\BudgetIncomeServiceInterface;
use App\Services\Interfaces\BudgetProvisionServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class BudgetServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use RefreshDatabase;

    public function testClonePassesNewBudgetIdAsFirstArgumentWhenCloningIncomes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $budgetExpenseService = Mockery::mock(BudgetExpenseServiceInterface::class);
        $budgetProvisionService = Mockery::mock(BudgetProvisionServiceInterface::class);
        $budgetIncomeService = Mockery::mock(BudgetIncomeServiceInterface::class);
        $budgetGoalService = Mockery::mock(BudgetGoalServiceInterface::class);
        $budgetShowData = Mockery::mock(BudgetShowDataInterface::class);
        $budgetCalculate = Mockery::mock(BudgetCalculateInterface::class);
        $budgetRelationPeriodBinder = Mockery::mock(BudgetRelationPeriodBinderInterface::class);
        $fixExpenseRepository = Mockery::mock(FixExpenseRepositoryInterface::class);
        $provisionRepository = Mockery::mock(ProvisionRepositoryInterface::class);
        $budgetRepository = Mockery::mock(BudgetRepositoryInterface::class);
        $creditCardInvoiceRepository = Mockery::mock(CreditCardInvoiceRepositoryInterface::class);
        $prepaidCardExtractRepository = Mockery::mock(PrepaidCardExtractRepositoryInterface::class);
        $financingInstallmentRepository = Mockery::mock(FinancingInstallmentRepositoryInterface::class);
        $budgetExpenseRepository = Mockery::mock(BudgetExpenseRepositoryInterface::class);
        $budgetProvisionRepository = Mockery::mock(BudgetProvisionRepositoryInterface::class);
        $budgetIncomeRepository = Mockery::mock(BudgetIncomeRepositoryInterface::class);
        $budgetGoalRepository = Mockery::mock(BudgetGoalRepositoryInterface::class);

        $sourceBudget = new Budget([
            'user_id' => $user->id,
            'year' => '2026',
            'month' => '05',
        ]);
        $sourceBudget->id = 10;
        $sourceBudget->setRelation('incomes', collect([
            new BudgetIncome([
                'description' => 'Salary',
                'date' => '2026-05-15',
                'value' => 3500,
                'remarks' => 'Monthly salary',
            ]),
        ]));
        $sourceBudget->setRelation('expenses', collect());
        $sourceBudget->setRelation('goals', collect());

        $newBudget = new Budget([
            'user_id' => $user->id,
            'year' => '2026',
            'month' => '06',
        ]);
        $newBudget->id = 20;

        $budgetRepository->shouldReceive('getOne')
            ->once()
            ->with(['year' => '2026', 'month' => '06', 'user_id' => $user->id])
            ->andReturn(null);
        $budgetRepository->shouldReceive('show')
            ->once()
            ->with(10, ['incomes.tags'])
            ->andReturn($sourceBudget);

        $budgetExpenseService->shouldNotReceive('create');
        $budgetProvisionService->shouldNotReceive('create');
        $budgetGoalService->shouldNotReceive('create');
        $provisionRepository->shouldNotReceive('get');

        $expectedDate = Carbon::parse('2026-05-15')->month('06')->format('y-m-d');

        $budgetIncomeService->shouldReceive('create')
            ->once()
            ->withArgs(function (
                int $budgetId,
                string $description,
                string $date,
                float|int $value,
                ?string $remarks,
                $tags
            ) use ($expectedDate) {
                $this->assertSame(20, $budgetId);
                $this->assertSame('Salary', $description);
                $this->assertSame($expectedDate, $date);
                $this->assertSame(3500.0, (float) $value);
                $this->assertSame('Monthly salary', $remarks);
                $this->assertTrue($tags === null || $tags instanceof Collection);

                return true;
            })
            ->andReturn(new BudgetIncome());

        $service = Mockery::mock(BudgetService::class, [
            $budgetExpenseService,
            $budgetProvisionService,
            $budgetIncomeService,
            $budgetGoalService,
            $budgetShowData,
            $budgetCalculate,
            $budgetRelationPeriodBinder,
            $fixExpenseRepository,
            $provisionRepository,
            $budgetRepository,
            $creditCardInvoiceRepository,
            $prepaidCardExtractRepository,
            $financingInstallmentRepository,
            $budgetExpenseRepository,
            $budgetProvisionRepository,
            $budgetIncomeRepository,
            $budgetGoalRepository,
        ])->makePartial();

        $service->shouldReceive('create')
            ->once()
            ->with($user->id, '2026', '06')
            ->andReturn($newBudget);

        $service->clone(10, '2026', '06', false, false, true, false);

        $this->assertTrue(true);
    }
}
