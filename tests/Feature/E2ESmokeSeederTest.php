<?php

namespace Tests\Feature;

use App\Models\Financing;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\E2ESmokeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class E2ESmokeSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_e2e_smoke_seed_creates_predictable_auth_navigation_data(): void
    {
        $this->seed(E2ESmokeSeeder::class);

        $user = User::query()
            ->where('email', E2ESmokeSeeder::email())
            ->firstOrFail();
        $budgetStartDate = CarbonImmutable::createFromDate(
            (int) E2ESmokeSeeder::budgetYear(),
            (int) E2ESmokeSeeder::budgetMonth(),
            1,
        )->startOfMonth();

        $this->assertDatabaseHas('budgets', [
            'user_id' => $user->id,
            'year' => E2ESmokeSeeder::budgetYear(),
            'month' => E2ESmokeSeeder::budgetMonth(),
        ]);

        $this->assertDatabaseHas('financings', [
            'user_id' => $user->id,
            'description' => 'E2E Financing',
            'start_date' => $budgetStartDate->toDateString(),
            'portion_total' => '012',
            'remarks' => 'E2E Smoke Financing',
        ]);

        $financing = Financing::query()
            ->where('user_id', $user->id)
            ->where('description', 'E2E Financing')
            ->firstOrFail();

        $this->assertDatabaseHas('financing_installments', [
            'financing_id' => $financing->id,
            'portion' => 1,
            'date' => $budgetStartDate->day(10)->toDateString(),
            'paid' => false,
        ]);

        $this->assertDatabaseHas('credit_cards', [
            'user_id' => $user->id,
            'name' => 'E2E Credit Card',
            'digits' => '4242',
        ]);

        $this->assertDatabaseHas('credit_card_invoices', [
            'year' => E2ESmokeSeeder::budgetYear(),
            'month' => E2ESmokeSeeder::budgetMonth(),
            'remarks' => 'E2E Smoke Invoice',
        ]);

        $this->assertDatabaseHas('prepaid_cards', [
            'user_id' => $user->id,
            'name' => 'E2E Prepaid Card',
            'digits' => '3030',
        ]);

        $this->assertDatabaseHas('prepaid_card_extracts', [
            'year' => E2ESmokeSeeder::budgetYear(),
            'month' => E2ESmokeSeeder::budgetMonth(),
            'remarks' => 'E2E Smoke Extract',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }
}
