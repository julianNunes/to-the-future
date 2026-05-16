<?php

namespace Tests\Feature\Http\Requests;

use App\Models\Financing;
use App\Models\FinancingInstallment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancingRequestTest extends TestCase
{
    use RefreshDatabase;

    public function testStoreRequiresCoreFields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/financing')
            ->post('/financing', [
                'description' => '',
                'start_date' => '',
                'total' => '',
                'fees_monthly' => '',
                'portion_total' => '',
                'start_date_installment' => '',
                'value_installment' => '',
            ])
            ->assertRedirect('/financing')
            ->assertSessionHasErrors([
                'description',
                'start_date',
                'total',
                'fees_monthly',
                'portion_total',
                'start_date_installment',
                'value_installment',
            ]);
    }

    public function testStoreNormalizesAndPersistsFinancingPayload(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/financing')
            ->post('/financing', [
                'description' => '  House Loan  ',
                'start_date' => '2026-05-15',
                'total' => 'R$ 1.200,50',
                'fees_monthly' => '1,75%',
                'portion_total' => '3',
                'remarks' => '  Initial contract  ',
                'start_date_installment' => '2026-06-01',
                'value_installment' => 'R$ 400,17',
            ])
            ->assertRedirect('/financing');

        $financing = Financing::query()->with('installments')->firstOrFail();

        $this->assertSame('House Loan', $financing->description);
        $this->assertSame('2026-05-15', $financing->start_date?->toDateString());
        $this->assertSame('Initial contract', $financing->remarks);
        $this->assertSame($user->id, $financing->user_id);
        $this->assertEquals(1200.50, (float) $financing->total);
        $this->assertEquals(1.75, (float) $financing->fees_monthly);
        $this->assertSame(3, (int) $financing->portion_total);
        $this->assertCount(3, $financing->installments);
        $this->assertSame('2026-06-01', $financing->installments[0]->date?->toDateString());
        $this->assertSame('2026-07-01', $financing->installments[1]->date?->toDateString());
        $this->assertEquals(400.17, (float) $financing->installments[0]->value);
    }

    public function testStoreRejectsInstallmentStartDateEarlierThanFinancingStartDate(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/financing')
            ->post('/financing', [
                'description' => 'House Loan',
                'start_date' => '2026-05-15',
                'total' => 'R$ 1.200,50',
                'fees_monthly' => '1,75%',
                'portion_total' => '3',
                'remarks' => 'Initial contract',
                'start_date_installment' => '2026-05-14',
                'value_installment' => 'R$ 400,17',
            ])
            ->assertRedirect('/financing')
            ->assertSessionHasErrors(['start_date_installment']);
    }

    public function testUpdateRequiresCoreFields(): void
    {
        $user = User::factory()->create();
        $financing = Financing::query()->create([
            'description' => 'Old',
            'start_date' => '2025-01-10',
            'total' => 5000,
            'fees_monthly' => 2.50,
            'portion_total' => 2,
            'remarks' => null,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->from('/financing')
            ->put("/financing/{$financing->id}", [
                'description' => '',
                'start_date' => '',
                'total' => '',
                'fees_monthly' => '',
            ])
            ->assertRedirect('/financing')
            ->assertSessionHasErrors(['description', 'start_date', 'total', 'fees_monthly']);
    }

    public function testUpdateNormalizesAndPersistsFinancingPayload(): void
    {
        $user = User::factory()->create();
        $financing = Financing::query()->create([
            'description' => 'Old',
            'start_date' => '2025-01-10',
            'total' => 5000,
            'fees_monthly' => 2.50,
            'portion_total' => 2,
            'remarks' => null,
            'user_id' => $user->id,
        ]);

        $openInstallment = FinancingInstallment::query()->create([
            'financing_id' => $financing->id,
            'value' => 2500,
            'portion' => 1,
            'date' => '2025-02-10',
            'paid' => false,
        ]);

        $paidInstallment = FinancingInstallment::query()->create([
            'financing_id' => $financing->id,
            'value' => 2500,
            'portion' => 2,
            'date' => '2025-03-10',
            'paid' => true,
        ]);

        $this->actingAs($user)
            ->from('/financing')
            ->put("/financing/{$financing->id}", [
                'description' => '  Renegotiated Loan  ',
                'start_date' => '2026-01-15',
                'total' => 'R$ 4.500,00',
                'fees_monthly' => '3,50%',
                'value_installment' => 'R$ 900,00',
                'remarks' => '  Updated contract  ',
            ])
            ->assertRedirect('/financing');

        $financing->refresh();
        $openInstallment->refresh();
        $paidInstallment->refresh();

        $this->assertSame('Renegotiated Loan', $financing->description);
        $this->assertSame('2026-01-15', $financing->start_date?->toDateString());
        $this->assertSame('Updated contract', $financing->remarks);
        $this->assertEquals(4500.00, (float) $financing->total);
        $this->assertEquals(3.50, (float) $financing->fees_monthly);
        $this->assertEquals(900.00, (float) $openInstallment->value);
        $this->assertEquals(2500.00, (float) $paidInstallment->value);
    }
}
