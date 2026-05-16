<?php

namespace Tests\Feature\Budget;

use App\Models\Budget;
use App\Models\BudgetExpense;
use App\Models\BudgetIncome;
use App\Models\BudgetProvision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetEntryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testBudgetExpenseStoreRequiresCoreFields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/budget')
            ->post('/budget-expense', [
                'budget_id' => '',
                'description' => '',
                'date' => '',
                'value' => '',
                'group' => '',
                'paid' => '',
            ])
            ->assertRedirect('/budget')
            ->assertSessionHasErrors(['budget_id', 'description', 'date', 'value', 'group', 'paid']);

        $this->assertDatabaseCount('budget_expenses', 0);
    }

    public function testBudgetExpenseUpdateNormalizesAndPersistsPayload(): void
    {
        $user = User::factory()->create();
        $shareUser = User::factory()->create();
        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'year' => '2026',
            'month' => '05',
        ]);
        $expense = BudgetExpense::query()->create([
            'budget_id' => $budget->id,
            'description' => 'Market',
            'date' => '2026-05-10',
            'value' => 100,
            'group' => 'MONTHLY',
            'paid' => false,
            'remarks' => null,
            'share_value' => null,
            'share_user_id' => null,
        ]);

        $this->actingAs($user)
            ->from('/budget')
            ->put("/budget-expense/{$expense->id}", [
                'description' => '  Updated Market  ',
                'date' => '2026-05-12',
                'value' => 'R$ 222,40',
                'group' => 'INDIVIDUAL',
                'paid' => '1',
                'remarks' => '  Shared  ',
                'share_value' => 'R$ 111,20',
                'share_user_id' => (string) $shareUser->id,
                'tags' => [
                    ['name' => 'FOOD'],
                ],
            ])
            ->assertRedirect('/budget');

        $expense->refresh()->load('tags');

        $this->assertSame('Updated Market', $expense->description);
        $this->assertSame('2026-05-12', $expense->date);
        $this->assertSame('INDIVIDUAL', $expense->group);
        $this->assertSame('Shared', $expense->remarks);
        $this->assertSame($shareUser->id, $expense->share_user_id);
        $this->assertSame(['FOOD'], $expense->tags->pluck('name')->all());
        $this->assertEquals(222.40, (float) $expense->value);
        $this->assertEquals(111.20, (float) $expense->share_value);
        $this->assertTrue((bool) $expense->paid);
    }

    public function testBudgetIncomeStoreRequiresCoreFields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/budget')
            ->post('/budget-income', [
                'budget_id' => '',
                'description' => '',
                'date' => '',
                'value' => '',
            ])
            ->assertRedirect('/budget')
            ->assertSessionHasErrors(['budget_id', 'description', 'date', 'value']);

        $this->assertDatabaseCount('bugdet_incomes', 0);
    }

    public function testBudgetIncomeUpdateNormalizesAndPersistsPayload(): void
    {
        $user = User::factory()->create();
        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'year' => '2026',
            'month' => '05',
        ]);
        $income = BudgetIncome::query()->create([
            'budget_id' => $budget->id,
            'description' => 'Salary',
            'date' => '2026-05-05',
            'value' => 2500,
            'remarks' => null,
        ]);

        $this->actingAs($user)
            ->from('/budget')
            ->put("/budget-income/{$income->id}", [
                'description' => '  Updated Salary  ',
                'date' => '2026-05-06',
                'value' => 'R$ 3200,45',
                'remarks' => '  Main income  ',
                'tags' => [
                    ['name' => 'WORK'],
                ],
            ])
            ->assertRedirect('/budget');

        $income->refresh()->load('tags');

        $this->assertSame('Updated Salary', $income->description);
        $this->assertSame('2026-05-06', $income->date);
        $this->assertSame('Main income', $income->remarks);
        $this->assertSame(['WORK'], $income->tags->pluck('name')->all());
        $this->assertEquals(3200.45, (float) $income->value);
    }

    public function testBudgetProvisionUpdateRequiresShareUserWhenShareValueIsProvided(): void
    {
        $user = User::factory()->create();
        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'year' => '2026',
            'month' => '05',
        ]);
        $provision = BudgetProvision::query()->create([
            'budget_id' => $budget->id,
            'description' => 'Emergency',
            'value' => 200,
            'group' => 'MONTHLY',
            'remarks' => null,
            'share_value' => null,
            'share_user_id' => null,
        ]);

        $this->actingAs($user)
            ->from('/budget')
            ->put("/budget-provision/{$provision->id}", [
                'description' => 'Emergency',
                'value' => '200',
                'group' => 'MONTHLY',
                'share_value' => 'R$ 80,00',
                'share_user_id' => '',
            ])
            ->assertRedirect('/budget')
            ->assertSessionHasErrors(['share_user_id']);
    }

    public function testBudgetProvisionUpdateNormalizesAndPersistsPayload(): void
    {
        $user = User::factory()->create();
        $shareUser = User::factory()->create();
        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'year' => '2026',
            'month' => '05',
        ]);
        $provision = BudgetProvision::query()->create([
            'budget_id' => $budget->id,
            'description' => 'Emergency',
            'value' => 250,
            'group' => 'MONTHLY',
            'remarks' => null,
            'share_value' => null,
            'share_user_id' => null,
        ]);

        $this->actingAs($user)
            ->from('/budget')
            ->put("/budget-provision/{$provision->id}", [
                'description' => '  Updated Emergency  ',
                'value' => 'R$ 410,70',
                'group' => 'WEEK_2',
                'remarks' => '  Shared  ',
                'share_value' => 'R$ 205,35',
                'share_user_id' => (string) $shareUser->id,
                'tags' => [
                    ['name' => 'SAFETY'],
                ],
            ])
            ->assertRedirect('/budget');

        $provision->refresh()->load('tags');

        $this->assertSame('Updated Emergency', $provision->description);
        $this->assertSame('WEEK_2', $provision->group);
        $this->assertSame('Shared', $provision->remarks);
        $this->assertSame($shareUser->id, $provision->share_user_id);
        $this->assertSame(['SAFETY'], $provision->tags->pluck('name')->all());
        $this->assertEquals(410.70, (float) $provision->value);
        $this->assertEquals(205.35, (float) $provision->share_value);
    }
}
