<?php

namespace Tests\Feature\Budget;

use App\Models\Budget;
use App\Models\ShareUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BudgetControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testBudgetStoreRequiresYearAndMonth(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/budget/2026')
            ->post('/budget', [
                'year' => '',
                'month' => '',
            ])
            ->assertSessionHasErrors(['year', 'month']);
    }

    public function testBudgetStorePersistsNormalizedPayload(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/budget/2026')
            ->post('/budget', [
                'year' => '2026',
                'month' => '5',
                'start_week_1' => '2026-05-01',
                'end_week_1' => '2026-05-07',
                'automaticGenerateYear' => '1',
                'includeFixExpenses' => '0',
                'includeProvisions' => '1',
            ])
            ->assertRedirect('/budget/2026');

        $this->assertDatabaseHas('budgets', [
            'user_id' => $user->id,
            'year' => '2026',
            'month' => '05',
            'start_week_1' => '2026-05-01',
            'end_week_1' => '2026-05-07',
        ]);
    }

    public function testBudgetUpdateRequiresPairedWeekDates(): void
    {
        $user = User::factory()->create();
        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'year' => '2026',
            'month' => '05',
        ]);

        $this->actingAs($user)
            ->from('/budget/2026')
            ->put("/budget/{$budget->id}", [
                'start_week_1' => '2026-05-01',
                'end_week_1' => '',
            ])
            ->assertSessionHasErrors(['end_week_1']);
    }

    public function testBudgetCloneNormalizesBooleanFlagsAndCreatesNewBudget(): void
    {
        $user = User::factory()->create();
        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'year' => '2026',
            'month' => '05',
        ]);

        $this->actingAs($user)
            ->from('/budget/2026')
            ->put("/budget/clone/{$budget->id}", [
                'year' => '2026',
                'month' => '6',
                'includeProvisions' => '0',
                'cloneBugdetExpenses' => '0',
                'cloneBugdetIncomes' => '0',
                'cloneBugdetGoals' => '0',
            ])
            ->assertRedirect('/budget/2026');

        $this->assertDatabaseHas('budgets', [
            'user_id' => $user->id,
            'year' => '2026',
            'month' => '06',
        ]);
    }

    public function testBudgetShowReturnsOwnerContractWithoutSharedBudget(): void
    {
        $user = User::factory()->create();
        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'year' => '2026',
            'month' => '05',
        ]);

        $response = $this->actingAs($user)->get(route('budget.show', ['id' => $budget->id]));

        $response->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Budget/Show')
            ->where('owner.budget.id', $budget->id)
            ->where('shareUser', null)
            ->where('share.budget', null)
            ->has('shareUsers', 0));
    }

    public function testBudgetShowIncludesSharedBudgetWhenShareUserIsConfigured(): void
    {
        $owner = User::factory()->create();
        $partner = User::factory()->create();

        $budget = Budget::factory()->create([
            'user_id' => $owner->id,
            'year' => '2026',
            'month' => '05',
        ]);
        $sharedBudget = Budget::factory()->create([
            'user_id' => $partner->id,
            'year' => '2026',
            'month' => '05',
        ]);

        ShareUser::query()->create([
            'user_id' => $owner->id,
            'share_user_id' => $partner->id,
        ]);

        $response = $this->actingAs($owner)->get(route('budget.show', ['id' => $budget->id]));

        $response->assertOk();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Budget/Show')
            ->where('owner.budget.id', $budget->id)
            ->where('shareUser.share_user_id', $partner->id)
            ->where('share.budget.id', $sharedBudget->id)
            ->where('share.budget.user_id', $partner->id)
            ->where('shareUsers.0.share_user_id', $partner->id));
    }
}
