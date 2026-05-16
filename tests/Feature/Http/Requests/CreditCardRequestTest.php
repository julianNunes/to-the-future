<?php

namespace Tests\Feature\Http\Requests;

use App\Models\CreditCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditCardRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testStoreRequiresCoreFields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/credit-card')
            ->post('/credit-card', [
                'name' => '',
                'digits' => '',
                'due_date' => '',
                'closing_date' => '',
                'is_active' => '',
            ])
            ->assertRedirect('/credit-card')
            ->assertSessionHasErrors(['name', 'digits', 'due_date', 'closing_date', 'is_active']);
    }

    public function testStoreNormalizesAndPersistsCreditCardPayload(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/credit-card')
            ->post('/credit-card', [
                'name' => '  Nubank  ',
                'digits' => ' 1234 ',
                'due_date' => '5',
                'closing_date' => '25',
                'is_active' => '1',
            ])
            ->assertRedirect('/credit-card');

        $card = CreditCard::query()
            ->where('user_id', $user->id)
            ->where('digits', '1234')
            ->firstOrFail();

        $this->assertSame('Nubank', $card->name);
        $this->assertSame('1234', $card->digits);
        $this->assertSame('5', $card->due_date);
        $this->assertSame('25', $card->closing_date);
        $this->assertSame($user->id, $card->user_id);
        $this->assertTrue((bool) $card->is_active);
    }

    public function testUpdateRejectsNonNumericDigits(): void
    {
        $user = User::factory()->create();
        $card = CreditCard::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->from('/credit-card')
            ->put("/credit-card/{$card->id}", [
                'name' => 'Card',
                'digits' => '12A4',
                'due_date' => '10',
                'closing_date' => '20',
                'is_active' => '0',
            ])
            ->assertRedirect('/credit-card')
            ->assertSessionHasErrors(['digits']);
    }

    public function testUpdateNormalizesAndPersistsCreditCardPayload(): void
    {
        $user = User::factory()->create();
        $card = CreditCard::factory()->create([
            'user_id' => $user->id,
            'name' => 'Old',
            'digits' => '1111',
            'due_date' => '8',
            'closing_date' => '20',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->from('/credit-card')
            ->put("/credit-card/{$card->id}", [
                'name' => '  Updated Card  ',
                'digits' => ' 9876 ',
                'due_date' => '12',
                'closing_date' => '28',
                'is_active' => '0',
            ])
            ->assertRedirect('/credit-card');

        $card->refresh();

        $this->assertSame('Updated Card', $card->name);
        $this->assertSame('9876', $card->digits);
        $this->assertSame('12', $card->due_date);
        $this->assertSame('28', $card->closing_date);
        $this->assertFalse((bool) $card->is_active);
    }
}
