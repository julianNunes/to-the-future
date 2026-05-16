<?php

namespace Tests\Feature\Http\Requests;

use App\Models\PrepaidCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrepaidCardRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testStoreRequiresCoreFields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/prepaid-card')
            ->post('/prepaid-card', [
                'name' => '',
                'digits' => '',
                'is_active' => '',
            ])
            ->assertRedirect('/prepaid-card')
            ->assertSessionHasErrors(['name', 'digits', 'is_active']);
    }

    public function testStoreNormalizesAndPersistsPrepaidCardPayload(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/prepaid-card')
            ->post('/prepaid-card', [
                'name' => '  Wallet  ',
                'digits' => ' 1234 ',
                'is_active' => '1',
            ])
            ->assertRedirect('/prepaid-card');

        $card = PrepaidCard::query()->firstOrFail();

        $this->assertSame('Wallet', $card->name);
        $this->assertSame('1234', $card->digits);
        $this->assertSame($user->id, $card->user_id);
        $this->assertTrue($card->is_active);
    }

    public function testUpdateRejectsNonNumericDigits(): void
    {
        $user = User::factory()->create();
        $card = PrepaidCard::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->from('/prepaid-card')
            ->put("/prepaid-card/{$card->id}", [
                'name' => 'Card',
                'digits' => '12A4',
                'is_active' => '0',
            ])
            ->assertRedirect('/prepaid-card')
            ->assertSessionHasErrors(['digits']);
    }

    public function testUpdateNormalizesAndPersistsPrepaidCardPayload(): void
    {
        $user = User::factory()->create();
        $card = PrepaidCard::factory()->create([
            'user_id' => $user->id,
            'name' => 'Old',
            'digits' => '1111',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->from('/prepaid-card')
            ->put("/prepaid-card/{$card->id}", [
                'name' => '  Updated Wallet  ',
                'digits' => ' 9876 ',
                'is_active' => '0',
            ])
            ->assertRedirect('/prepaid-card');

        $card->refresh();

        $this->assertSame('Updated Wallet', $card->name);
        $this->assertSame('9876', $card->digits);
        $this->assertFalse($card->is_active);
    }
}
