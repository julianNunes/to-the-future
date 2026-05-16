<?php

namespace Tests\Feature\Financing;

use App\Models\Financing;
use App\Models\FinancingInstallment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancingInstallmentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testOwnerCanUpdateOwnedInstallmentWithNormalizedPaidPayload(): void
    {
        $owner = User::query()->create([
            'name' => 'Installment Owner',
            'email' => uniqid('installment-owner-', true) . '@example.com',
            'password' => 'password-owner',
        ]);

        $financing = Financing::query()->create([
            'description' => 'Owner Financing',
            'start_date' => '2026-08-01',
            'total' => 900.80,
            'fees_monthly' => 1.4,
            'portion_total' => 2,
            'remarks' => 'Owner remarks',
            'user_id' => $owner->id,
        ]);

        $installment = FinancingInstallment::query()->create([
            'financing_id' => $financing->id,
            'value' => 450.40,
            'portion' => 1,
            'date' => '2026-08-05',
            'payment_date' => null,
            'paid' => false,
            'paid_value' => null,
        ]);

        $response = $this->actingAs($owner)
            ->from('/financing/' . $financing->id . '/installment')
            ->put('/financing/installment/' . $installment->id, [
                'date' => '2026-8-10',
                'value' => 'R$ 450,90',
                'paid' => '1',
                'payment_date' => '2026-8-12',
                'paid_value' => 'R$ 450,90',
            ]);

        $response->assertRedirect('/financing/' . $financing->id . '/installment');
        $response->assertSessionHas('success', 'default.sucess-update');

        $installment->refresh();

        $this->assertSame('2026-08-10', $installment->date?->toDateString());
        $this->assertSame('2026-08-12', $installment->payment_date?->toDateString());
        $this->assertEquals(450.90, (float) $installment->value);
        $this->assertTrue($installment->paid);
        $this->assertEquals(450.90, (float) $installment->paid_value);
    }

    public function testIntruderCannotUpdateAnotherUsersInstallment(): void
    {
        $owner = User::query()->create([
            'name' => 'Installment Target Owner',
            'email' => uniqid('installment-target-owner-', true) . '@example.com',
            'password' => 'password-target-owner',
        ]);

        $intruder = User::query()->create([
            'name' => 'Installment Intruder',
            'email' => uniqid('installment-intruder-', true) . '@example.com',
            'password' => 'password-intruder',
        ]);

        $financing = Financing::query()->create([
            'description' => 'Protected Financing',
            'start_date' => '2026-10-01',
            'total' => 1500,
            'fees_monthly' => 1.8,
            'portion_total' => 3,
            'remarks' => 'Protected remarks',
            'user_id' => $owner->id,
        ]);

        $installment = FinancingInstallment::query()->create([
            'financing_id' => $financing->id,
            'value' => 500,
            'portion' => 1,
            'date' => '2026-10-10',
            'payment_date' => null,
            'paid' => false,
            'paid_value' => null,
        ]);

        $response = $this->actingAs($intruder)->put('/financing/installment/' . $installment->id, [
            'date' => '2026-10-12',
            'value' => 'R$ 510,00',
            'paid' => '1',
            'payment_date' => '2026-10-13',
            'paid_value' => 'R$ 510,00',
        ]);

        $response->assertForbidden();
        $installment->refresh();

        $this->assertSame($financing->id, $installment->financing_id);
        $this->assertSame(1, $installment->portion);
        $this->assertSame('2026-10-10', $installment->date?->toDateString());
        $this->assertNull($installment->payment_date);
        $this->assertFalse($installment->paid);
        $this->assertNull($installment->paid_value);
        $this->assertEquals(500.00, (float) $installment->value);
    }
}
