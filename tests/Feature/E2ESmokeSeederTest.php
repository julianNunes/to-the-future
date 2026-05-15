<?php

namespace Tests\Feature;

use App\Models\User;
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

        $this->assertDatabaseHas('budgets', [
            'user_id' => $user->id,
            'year' => E2ESmokeSeeder::budgetYear(),
            'month' => E2ESmokeSeeder::budgetMonth(),
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
