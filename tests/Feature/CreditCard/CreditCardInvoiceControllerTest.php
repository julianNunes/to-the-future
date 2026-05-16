<?php

namespace Tests\Feature\CreditCard;

use App\Models\CreditCard;
use App\Models\CreditCardInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditCardInvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testInvoiceStoreRequiresCoreFields(): void
    {
        $user = User::factory()->create();
        $card = CreditCard::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->from("/credit-card/{$card->id}/invoice")
            ->post('/credit-card/invoice', [
                'due_date' => '',
                'closing_date' => '',
                'year' => '',
                'month' => '',
                'credit_card_id' => '',
            ])
            ->assertRedirect("/credit-card/{$card->id}/invoice")
            ->assertSessionHasErrors(['due_date', 'closing_date', 'year', 'month', 'credit_card_id']);

        $this->assertDatabaseCount('credit_card_invoices', 0);
    }

    public function testInvoiceStoreNormalizesAndPersistsPayload(): void
    {
        $user = User::factory()->create();
        $card = CreditCard::factory()->create([
            'user_id' => $user->id,
            'due_date' => '10',
            'closing_date' => '05',
        ]);

        $this->actingAs($user)
            ->from("/credit-card/{$card->id}/invoice")
            ->post('/credit-card/invoice', [
                'due_date' => ' 2026-05-10 ',
                'closing_date' => ' 2026-05-05 ',
                'year' => ' 2026 ',
                'month' => '5',
                'credit_card_id' => (string) $card->id,
                'automatic_generate' => '0',
            ])
            ->assertRedirect("/credit-card/{$card->id}/invoice");

        $invoice = CreditCardInvoice::query()->firstOrFail();

        $this->assertSame('2026-05-10', $invoice->due_date?->toDateString());
        $this->assertSame('2026-05-05', $invoice->closing_date?->toDateString());
        $this->assertSame('2026', $invoice->year);
        $this->assertSame('05', $invoice->month);
        $this->assertSame($card->id, $invoice->credit_card_id);
        $this->assertFalse($invoice->closed);
    }

    public function testInvoiceStoreRejectsDuplicatePeriodForSameCard(): void
    {
        $user = User::factory()->create();
        $card = CreditCard::factory()->create(['user_id' => $user->id]);

        CreditCardInvoice::factory()->create([
            'credit_card_id' => $card->id,
            'year' => '2026',
            'month' => '05',
            'due_date' => '2026-05-10',
            'closing_date' => '2026-05-05',
        ]);

        $this->actingAs($user)
            ->from("/credit-card/{$card->id}/invoice")
            ->post('/credit-card/invoice', [
                'due_date' => '2026-05-10',
                'closing_date' => '2026-05-05',
                'year' => '2026',
                'month' => '05',
                'credit_card_id' => (string) $card->id,
                'automatic_generate' => '0',
            ])
            ->assertRedirect("/credit-card/{$card->id}/invoice")
            ->assertSessionHasErrors(['error']);

        $this->assertDatabaseCount('credit_card_invoices', 1);
    }

    public function testInvoiceUpdateNormalizesClosedFlag(): void
    {
        $user = User::factory()->create();
        $card = CreditCard::factory()->create(['user_id' => $user->id]);
        $invoice = CreditCardInvoice::factory()->create([
            'credit_card_id' => $card->id,
            'closed' => false,
        ]);

        $this->actingAs($user)
            ->from("/credit-card/{$card->id}/invoice")
            ->put("/credit-card/invoice/{$invoice->id}", [
                'closed' => '1',
            ])
            ->assertRedirect("/credit-card/{$card->id}/invoice");

        $invoice->refresh();

        $this->assertTrue($invoice->closed);
    }
}
