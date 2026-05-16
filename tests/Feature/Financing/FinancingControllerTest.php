<?php

namespace Tests\Feature\Financing;

use App\Models\Financing;
use App\Models\FinancingInstallment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testOwnerCanDeleteOwnedFinancingAndItsInstallments(): void
    {
        $owner = User::query()->create([
            'name' => 'Financing Owner',
            'email' => uniqid('financing-owner-', true) . '@example.com',
            'password' => 'password-owner',
        ]);

        $financing = Financing::query()->create([
            'description' => 'Owner Financing',
            'start_date' => '2026-08-01',
            'total' => 1200.50,
            'fees_monthly' => 1.75,
            'portion_total' => 2,
            'remarks' => 'Owner remarks',
            'user_id' => $owner->id,
        ]);

        $firstInstallment = FinancingInstallment::query()->create([
            'financing_id' => $financing->id,
            'value' => 600.25,
            'portion' => 1,
            'date' => '2026-08-10',
            'paid' => false,
        ]);

        $secondInstallment = FinancingInstallment::query()->create([
            'financing_id' => $financing->id,
            'value' => 600.25,
            'portion' => 2,
            'date' => '2026-09-10',
            'paid' => false,
        ]);

        $response = $this->actingAs($owner)
            ->from('/financing')
            ->delete('/financing/' . $financing->id);

        $response->assertRedirect('/financing');
        $response->assertSessionHas('success', 'default.sucess-delete');
        $this->assertDatabaseMissing('financing_installments', ['id' => $firstInstallment->id]);
        $this->assertDatabaseMissing('financing_installments', ['id' => $secondInstallment->id]);
        $this->assertDatabaseMissing('financings', ['id' => $financing->id]);
    }

    public function testIntruderCannotUpdateAnotherUsersFinancing(): void
    {
        $owner = User::query()->create([
            'name' => 'Financing Target Owner',
            'email' => uniqid('financing-target-owner-', true) . '@example.com',
            'password' => 'password-target-owner',
        ]);

        $intruder = User::query()->create([
            'name' => 'Financing Intruder',
            'email' => uniqid('financing-intruder-', true) . '@example.com',
            'password' => 'password-intruder',
        ]);

        $financing = Financing::query()->create([
            'description' => 'Protected Financing',
            'start_date' => '2026-07-15',
            'total' => 5000,
            'fees_monthly' => 2.5,
            'portion_total' => 5,
            'remarks' => 'Protected remark',
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($intruder)->put('/financing/' . $financing->id, [
            'description' => 'Intruder Change',
            'start_date' => '2026-08-01',
            'total' => '6000',
            'fees_monthly' => '3.0',
            'remarks' => 'Attempt',
        ]);

        $response->assertForbidden();
        $financing->refresh();

        $this->assertSame('Protected Financing', $financing->description);
        $this->assertSame('2026-07-15', $financing->start_date?->toDateString());
        $this->assertSame('Protected remark', $financing->remarks);
        $this->assertSame($owner->id, $financing->user_id);
        $this->assertEquals(5000.0, (float) $financing->total);
        $this->assertEquals(2.5, (float) $financing->fees_monthly);
    }

    public function testIntruderCannotDeleteAnotherUsersFinancing(): void
    {
        $owner = User::query()->create([
            'name' => 'Financing Delete Owner',
            'email' => uniqid('financing-delete-owner-', true) . '@example.com',
            'password' => 'password-delete-owner',
        ]);

        $intruder = User::query()->create([
            'name' => 'Financing Delete Intruder',
            'email' => uniqid('financing-delete-intruder-', true) . '@example.com',
            'password' => 'password-delete-intruder',
        ]);

        $financing = Financing::query()->create([
            'description' => 'Delete Protected Financing',
            'start_date' => '2026-09-05',
            'total' => 3200,
            'fees_monthly' => 1.9,
            'portion_total' => 4,
            'remarks' => 'Delete protected remark',
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($intruder)->delete('/financing/' . $financing->id);

        $response->assertForbidden();
        $this->assertDatabaseHas('financings', ['id' => $financing->id]);
    }
}
