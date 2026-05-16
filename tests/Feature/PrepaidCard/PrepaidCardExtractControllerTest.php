<?php

namespace Tests\Feature\PrepaidCard;

use App\Models\PrepaidCard;
use App\Models\PrepaidCardExtract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrepaidCardExtractControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testExtractStoreRequiresCoreFields(): void
    {
        $user = User::factory()->create();
        $prepaidCard = PrepaidCard::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->from("/prepaid-card/{$prepaidCard->id}/extract")
            ->post('/prepaid-card/extract', [
                'year' => '',
                'month' => '',
                'credit' => '',
                'credit_date' => '',
                'prepaid_card_id' => '',
            ])
            ->assertRedirect("/prepaid-card/{$prepaidCard->id}/extract")
            ->assertSessionHasErrors(['year', 'month', 'credit', 'credit_date', 'prepaid_card_id']);

        $this->assertDatabaseCount('prepaid_card_extracts', 0);
    }

    public function testExtractStoreNormalizesAndPersistsPayload(): void
    {
        $user = User::factory()->create();
        $prepaidCard = PrepaidCard::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->from("/prepaid-card/{$prepaidCard->id}/extract")
            ->post('/prepaid-card/extract', [
                'year' => ' 2026 ',
                'month' => '5',
                'credit' => ' R$ 1.234,56 ',
                'credit_date' => ' 2026-5-7 ',
                'remarks' => '  Initial load  ',
                'prepaid_card_id' => (string) $prepaidCard->id,
            ])
            ->assertRedirect("/prepaid-card/{$prepaidCard->id}/extract");

        $extract = PrepaidCardExtract::query()->firstOrFail();

        $this->assertSame('2026', $extract->year);
        $this->assertSame('05', $extract->month);
        $this->assertEqualsWithDelta(1234.56, (float) $extract->credit, 0.001);
        $this->assertSame('2026-05-07', $extract->credit_date?->toDateString());
        $this->assertSame('Initial load', $extract->remarks);
        $this->assertSame($prepaidCard->id, $extract->prepaid_card_id);
    }

    public function testExtractStoreRejectsDuplicatePeriodForSameCard(): void
    {
        $user = User::factory()->create();
        $prepaidCard = PrepaidCard::factory()->create(['user_id' => $user->id]);

        PrepaidCardExtract::factory()->create([
            'prepaid_card_id' => $prepaidCard->id,
            'year' => '2026',
            'month' => '05',
            'credit_date' => '2026-05-07',
        ]);

        $this->actingAs($user)
            ->from("/prepaid-card/{$prepaidCard->id}/extract")
            ->post('/prepaid-card/extract', [
                'year' => '2026',
                'month' => '05',
                'credit' => '1500',
                'credit_date' => '2026-05-08',
                'remarks' => 'Duplicate',
                'prepaid_card_id' => (string) $prepaidCard->id,
            ])
            ->assertRedirect("/prepaid-card/{$prepaidCard->id}/extract")
            ->assertSessionHasErrors(['error']);

        $this->assertDatabaseCount('prepaid_card_extracts', 1);
    }

    public function testExtractUpdateNormalizesAndPersistsPayload(): void
    {
        $user = User::factory()->create();
        $prepaidCard = PrepaidCard::factory()->create(['user_id' => $user->id]);
        $extract = PrepaidCardExtract::factory()->create([
            'prepaid_card_id' => $prepaidCard->id,
            'credit' => 500,
            'credit_date' => '2026-05-07',
            'remarks' => 'Old note',
        ]);

        $this->actingAs($user)
            ->from("/prepaid-card/{$prepaidCard->id}/extract")
            ->put("/prepaid-card/extract/{$extract->id}", [
                'credit' => ' R$ 2.500,40 ',
                'credit_date' => ' 2026-6-9 ',
                'remarks' => '  Updated note  ',
            ])
            ->assertRedirect("/prepaid-card/{$prepaidCard->id}/extract");

        $extract->refresh();

        $this->assertEqualsWithDelta(2500.40, (float) $extract->credit, 0.001);
        $this->assertSame('2026-06-09', $extract->credit_date?->toDateString());
        $this->assertSame('Updated note', $extract->remarks);
    }

    public function testExtractDeleteRemovesOwnedExtract(): void
    {
        $user = User::factory()->create();
        $prepaidCard = PrepaidCard::factory()->create(['user_id' => $user->id]);
        $extract = PrepaidCardExtract::factory()->create([
            'prepaid_card_id' => $prepaidCard->id,
            'budget_id' => null,
        ]);

        $this->actingAs($user)
            ->from("/prepaid-card/{$prepaidCard->id}/extract")
            ->delete("/prepaid-card/extract/{$extract->id}")
            ->assertRedirect("/prepaid-card/{$prepaidCard->id}/extract");

        $this->assertDatabaseMissing('prepaid_card_extracts', [
            'id' => $extract->id,
        ]);
    }

    public function testUserCannotCreateExtractForAnotherUsersCard(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $prepaidCard = PrepaidCard::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($intruder)
            ->from("/prepaid-card/{$prepaidCard->id}/extract")
            ->post('/prepaid-card/extract', [
                'year' => '2026',
                'month' => '05',
                'credit' => 1000,
                'credit_date' => '2026-05-07',
                'prepaid_card_id' => $prepaidCard->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('prepaid_card_extracts', [
            'prepaid_card_id' => $prepaidCard->id,
            'year' => '2026',
            'month' => '05',
            'credit_date' => '2026-05-07',
        ]);
    }
}
