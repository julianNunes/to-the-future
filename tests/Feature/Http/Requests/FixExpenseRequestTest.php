<?php

namespace Tests\Feature\Http\Requests;

use App\Models\FixExpense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixExpenseRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testStoreRequiresCoreFields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/fix-expense')
            ->post('/fix-expense', [
                'description' => '',
                'due_date' => '',
                'value' => '',
            ])
            ->assertRedirect('/fix-expense')
            ->assertSessionHasErrors(['description', 'due_date', 'value']);

        $this->assertDatabaseCount('fix_expenses', 0);
    }

    public function testStoreNormalizesAndPersistsFixExpensePayload(): void
    {
        $user = User::factory()->create();
        $shareUser = User::factory()->create();

        $this->actingAs($user)
            ->from('/fix-expense')
            ->post('/fix-expense', [
                'description' => '  Rent  ',
                'due_date' => '5',
                'value' => 'R$ 150,50',
                'remarks' => '  Home  ',
                'share_value' => 'R$ 50,00',
                'share_user_id' => (string) $shareUser->id,
                'tags' => [
                    ['name' => 'HOME'],
                ],
            ])
            ->assertRedirect('/fix-expense');

        $expense = FixExpense::query()->with('tags')->firstOrFail();

        $this->assertSame('Rent', $expense->description);
        $this->assertSame('05', $expense->due_date);
        $this->assertSame('Home', $expense->remarks);
        $this->assertSame($user->id, $expense->user_id);
        $this->assertSame($shareUser->id, $expense->share_user_id);
        $this->assertSame(['HOME'], $expense->tags->pluck('name')->all());
        $this->assertEquals(150.50, (float) $expense->value);
        $this->assertEquals(50.00, (float) $expense->share_value);
    }

    public function testUpdateRequiresShareValueWhenShareUserIsProvided(): void
    {
        $user = User::factory()->create();
        $shareUser = User::factory()->create();
        $expense = FixExpense::query()->create([
            'description' => 'Internet',
            'due_date' => '10',
            'value' => 100,
            'remarks' => null,
            'share_value' => null,
            'share_user_id' => null,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->from('/fix-expense')
            ->put("/fix-expense/{$expense->id}", [
                'description' => 'Internet',
                'due_date' => '10',
                'value' => '100',
                'share_value' => '0',
                'share_user_id' => (string) $shareUser->id,
            ])
            ->assertRedirect('/fix-expense')
            ->assertSessionHasErrors(['share_value']);
    }

    public function testUpdateNormalizesAndPersistsFixExpensePayload(): void
    {
        $user = User::factory()->create();
        $shareUser = User::factory()->create();
        $expense = FixExpense::query()->create([
            'description' => 'Internet',
            'due_date' => '10',
            'value' => 100,
            'remarks' => null,
            'share_value' => null,
            'share_user_id' => null,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->from('/fix-expense')
            ->put("/fix-expense/{$expense->id}", [
                'description' => '  Updated Internet  ',
                'due_date' => '9',
                'value' => 'R$ 222,40',
                'remarks' => '  Shared  ',
                'share_value' => 'R$ 111,20',
                'share_user_id' => (string) $shareUser->id,
                'tags' => [
                    ['name' => 'BILL'],
                ],
            ])
            ->assertRedirect('/fix-expense');

        $expense->refresh()->load('tags');

        $this->assertSame('Updated Internet', $expense->description);
        $this->assertSame('09', $expense->due_date);
        $this->assertSame('Shared', $expense->remarks);
        $this->assertSame($shareUser->id, $expense->share_user_id);
        $this->assertSame(['BILL'], $expense->tags->pluck('name')->all());
        $this->assertEquals(222.40, (float) $expense->value);
        $this->assertEquals(111.20, (float) $expense->share_value);
    }
}
