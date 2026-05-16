<?php

namespace Tests\Feature\FixExpense;

use App\Models\FixExpense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixExpenseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_delete_an_owned_fix_expense(): void
    {
        $owner = User::query()->create([
            'name' => 'Fix Expense Owner',
            'email' => 'fix-expense-owner@example.com',
            'password' => 'password-owner',
        ]);

        $expense = FixExpense::query()->create([
            'description' => 'Owner Expense',
            'due_date' => '10',
            'value' => 100,
            'remarks' => 'Owner Remark',
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($owner)
            ->from('/fix-expense')
            ->delete('/fix-expense/' . $expense->id);

        $response->assertRedirect('/fix-expense');
        $response->assertSessionHas('success', 'default.sucess-delete');
        $this->assertDatabaseMissing('fix_expenses', ['id' => $expense->id]);
    }

    public function test_intruder_cannot_update_another_users_fix_expense(): void
    {
        $owner = User::query()->create([
            'name' => 'Fix Expense Target Owner',
            'email' => 'fix-expense-target-owner@example.com',
            'password' => 'password-target-owner',
        ]);

        $intruder = User::query()->create([
            'name' => 'Fix Expense Intruder',
            'email' => 'fix-expense-intruder@example.com',
            'password' => 'password-intruder',
        ]);

        $expense = FixExpense::query()->create([
            'description' => 'Protected Expense',
            'due_date' => '08',
            'value' => 200,
            'remarks' => 'Protected Remark',
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($intruder)->put('/fix-expense/' . $expense->id, [
            'description' => 'Intruder Change',
            'due_date' => '12',
            'value' => 250,
            'remarks' => 'Attempt',
            'tags' => [],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('fix_expenses', [
            'id' => $expense->id,
            'description' => 'Protected Expense',
            'value' => 200,
            'remarks' => 'Protected Remark',
        ]);
    }

    public function test_intruder_cannot_delete_another_users_fix_expense(): void
    {
        $owner = User::query()->create([
            'name' => 'Fix Expense Delete Owner',
            'email' => 'fix-expense-delete-owner@example.com',
            'password' => 'password-delete-owner',
        ]);

        $intruder = User::query()->create([
            'name' => 'Fix Expense Delete Intruder',
            'email' => 'fix-expense-delete-intruder@example.com',
            'password' => 'password-delete-intruder',
        ]);

        $expense = FixExpense::query()->create([
            'description' => 'Delete Protected Expense',
            'due_date' => '15',
            'value' => 300,
            'remarks' => 'Delete Protected Remark',
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($intruder)->delete('/fix-expense/' . $expense->id);

        $response->assertForbidden();
        $this->assertDatabaseHas('fix_expenses', ['id' => $expense->id]);
    }
}
