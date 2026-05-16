<?php

namespace Tests\Feature\PrepaidCard;

use App\Models\PrepaidCard;
use App\Models\PrepaidCardExtract;
use App\Models\PrepaidCardExtractExpense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrepaidCardExtractExpenseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testExtractExpenseStoreRequiresCoreFields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/prepaid-card/extract/1')
            ->post('/prepaid-card/extract/expense', [
                'prepaid_card_id' => '',
                'extract_id' => '',
                'description' => '',
                'date' => '',
                'value' => '',
                'group' => '',
            ])
            ->assertRedirect('/prepaid-card/extract/1')
            ->assertSessionHasErrors(['prepaid_card_id', 'extract_id', 'description', 'date', 'value', 'group']);

        $this->assertDatabaseCount('prepaid_card_extract_expenses', 0);
    }

    public function testExtractExpenseStoreNormalizesAndPersistsPayload(): void
    {
        $user = User::factory()->create();
        $shareUser = User::factory()->create();
        $prepaidCard = PrepaidCard::factory()->create(['user_id' => $user->id]);
        $extract = PrepaidCardExtract::factory()->create([
            'prepaid_card_id' => $prepaidCard->id,
            'budget_id' => null,
            'year' => '2026',
            'month' => '05',
        ]);

        $this->actingAs($user)
            ->from("/prepaid-card/extract/{$extract->id}")
            ->post('/prepaid-card/extract/expense', [
                'prepaid_card_id' => (string) $prepaidCard->id,
                'extract_id' => (string) $extract->id,
                'description' => '  Fuel Refill  ',
                'date' => ' 2026-5-10 ',
                'value' => 'R$ 120,40',
                'group' => ' WEEK_1 ',
                'remarks' => '  Shared expense  ',
                'share_value' => 'R$ 60,20',
                'share_user_id' => (string) $shareUser->id,
                'tags' => [
                    ['name' => 'FUEL'],
                ],
            ])
            ->assertRedirect("/prepaid-card/extract/{$extract->id}");

        $expense = PrepaidCardExtractExpense::query()->with('tags')->firstOrFail();

        $this->assertSame('Fuel Refill', $expense->description);
        $this->assertSame('2026-05-10', $expense->date?->toDateString());
        $this->assertSame('WEEK_1', $expense->group);
        $this->assertSame('Shared expense', $expense->remarks);
        $this->assertSame($shareUser->id, $expense->share_user_id);
        $this->assertSame(['FUEL'], $expense->tags->pluck('name')->all());
        $this->assertEquals(120.40, (float) $expense->value);
        $this->assertEquals(60.20, (float) $expense->share_value);
    }

    public function testExtractExpenseUpdateNormalizesAndPersistsPayload(): void
    {
        $user = User::factory()->create();
        $shareUser = User::factory()->create();
        $prepaidCard = PrepaidCard::factory()->create(['user_id' => $user->id]);
        $extract = PrepaidCardExtract::factory()->create([
            'prepaid_card_id' => $prepaidCard->id,
            'budget_id' => null,
        ]);
        $expense = PrepaidCardExtractExpense::factory()->create([
            'extract_id' => $extract->id,
            'description' => 'Old fuel',
            'date' => '2026-05-10',
            'value' => 100,
            'group' => 'WEEK_1',
            'remarks' => null,
            'share_value' => null,
            'share_user_id' => null,
        ]);

        $this->actingAs($user)
            ->from("/prepaid-card/extract/{$extract->id}")
            ->put("/prepaid-card/extract/expense/{$expense->id}", [
                'prepaid_card_id' => (string) $prepaidCard->id,
                'extract_id' => (string) $extract->id,
                'description' => '  Updated Fuel  ',
                'date' => '2026-05-12',
                'value' => 'R$ 333,70',
                'group' => 'WEEK_2',
                'remarks' => '  Updated note  ',
                'share_value' => 'R$ 111,20',
                'share_user_id' => (string) $shareUser->id,
                'tags' => [
                    ['name' => 'UPDATED'],
                ],
            ])
            ->assertRedirect("/prepaid-card/extract/{$extract->id}");

        $expense->refresh()->load('tags');

        $this->assertSame('Updated Fuel', $expense->description);
        $this->assertSame('2026-05-12', $expense->date?->toDateString());
        $this->assertSame('WEEK_2', $expense->group);
        $this->assertSame('Updated note', $expense->remarks);
        $this->assertSame($shareUser->id, $expense->share_user_id);
        $this->assertSame(['UPDATED'], $expense->tags->pluck('name')->all());
        $this->assertEquals(333.70, (float) $expense->value);
        $this->assertEquals(111.20, (float) $expense->share_value);
    }

    public function testExtractExpenseImportNormalizesAndPersistsParsedRows(): void
    {
        $user = User::factory()->create();
        $shareUser = User::factory()->create();
        $prepaidCard = PrepaidCard::factory()->create(['user_id' => $user->id]);
        $extract = PrepaidCardExtract::factory()->create([
            'prepaid_card_id' => $prepaidCard->id,
            'budget_id' => null,
        ]);

        $this->actingAs($user)
            ->from("/prepaid-card/extract/{$extract->id}")
            ->post('/prepaid-card/extract/expense/import-excel', [
                'extract_id' => (string) $extract->id,
                'data' => [
                    [
                        'description' => '  Imported Fuel  ',
                        'date' => ' 2026-5-9 ',
                        'value' => 'R$ 80,50',
                        'group' => ' WEEK_3 ',
                        'remarks' => '  Imported note  ',
                        'share_value' => 'R$ 20,10',
                        'share_user_id' => (string) $shareUser->id,
                        'tags' => [
                            ['name' => 'IMPORT'],
                        ],
                    ],
                ],
            ])
            ->assertRedirect("/prepaid-card/extract/{$extract->id}");

        $expense = PrepaidCardExtractExpense::query()->with('tags')->firstOrFail();

        $this->assertSame('Imported Fuel', $expense->description);
        $this->assertSame('2026-05-09', $expense->date?->toDateString());
        $this->assertSame('Imported note', $expense->remarks);
        $this->assertSame('WEEK_3', $expense->group);
        $this->assertSame($shareUser->id, $expense->share_user_id);
        $this->assertSame(['IMPORT'], $expense->tags->pluck('name')->all());
        $this->assertEquals(80.50, (float) $expense->value);
        $this->assertEquals(20.10, (float) $expense->share_value);
    }

    public function testExtractExpenseDeleteRemovesExpense(): void
    {
        $user = User::factory()->create();
        $prepaidCard = PrepaidCard::factory()->create(['user_id' => $user->id]);
        $extract = PrepaidCardExtract::factory()->create([
            'prepaid_card_id' => $prepaidCard->id,
            'budget_id' => null,
        ]);
        $expense = PrepaidCardExtractExpense::factory()->create([
            'extract_id' => $extract->id,
        ]);

        $this->actingAs($user)
            ->from("/prepaid-card/extract/{$extract->id}")
            ->delete("/prepaid-card/extract/expense/{$expense->id}")
            ->assertRedirect("/prepaid-card/extract/{$extract->id}");

        $this->assertDatabaseMissing('prepaid_card_extract_expenses', ['id' => $expense->id]);
    }

    public function testExtractExpenseSearchReturnsOwnedDescriptions(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownerCard = PrepaidCard::factory()->create(['user_id' => $owner->id]);
        $ownerExtract = PrepaidCardExtract::factory()->create([
            'prepaid_card_id' => $ownerCard->id,
            'budget_id' => null,
        ]);
        PrepaidCardExtractExpense::factory()->create([
            'extract_id' => $ownerExtract->id,
            'description' => 'OWN GAS',
        ]);

        $otherCard = PrepaidCard::factory()->create(['user_id' => $otherUser->id]);
        $otherExtract = PrepaidCardExtract::factory()->create([
            'prepaid_card_id' => $otherCard->id,
            'budget_id' => null,
        ]);
        PrepaidCardExtractExpense::factory()->create([
            'extract_id' => $otherExtract->id,
            'description' => 'OTHER GAS',
        ]);

        $this->actingAs($owner)
            ->get('/prepaid-card/extract/expense/search/GAS')
            ->assertOk()
            ->assertJsonFragment(['description' => 'OWN GAS'])
            ->assertJsonMissing(['description' => 'OTHER GAS']);
    }
}
