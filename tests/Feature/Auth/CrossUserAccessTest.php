<?php

namespace Tests\Feature\Auth;

use App\Models\Budget;
use App\Models\BudgetProvision;
use App\Models\CreditCard;
use App\Models\CreditCardInvoice;
use App\Models\CreditCardInvoiceExpense;
use App\Models\PrepaidCard;
use App\Models\PrepaidCardExtract;
use App\Models\PrepaidCardExtractExpense;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossUserAccessTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCannotViewAnotherUsersBudget(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $budget = Budget::factory()->create([
            'user_id' => $owner->id,
            'year' => '2026',
            'month' => '05',
        ]);

        $this->actingAs($intruder)
            ->get(route('budget.show', ['id' => $budget->id]))
            ->assertForbidden();
    }

    public function testUserCanViewOwnBudget(): void
    {
        $owner = User::factory()->create();
        $budget = Budget::factory()->create([
            'user_id' => $owner->id,
            'year' => '2026',
            'month' => '05',
        ]);

        $this->actingAs($owner)
            ->get(route('budget.show', ['id' => $budget->id]))
            ->assertOk();
    }

    public function testUserCannotDeleteAnotherUsersBudget(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $budget = Budget::factory()->create([
            'user_id' => $owner->id,
            'year' => '2026',
            'month' => '05',
        ]);

        $this->actingAs($intruder)
            ->delete("/budget/{$budget->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('budgets', ['id' => $budget->id]);
    }

    public function testUserCannotCreateGoalForAnotherUsersBudget(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $budget = Budget::factory()->create([
            'user_id' => $owner->id,
            'year' => '2026',
            'month' => '05',
        ]);

        $this->actingAs($intruder)
            ->post('/budget-goal', [
                'description' => 'Emergency Fund',
                'value' => 500,
                'tags' => [['name' => 'SAVINGS']],
                'count_share' => 0,
                'budget_id' => $budget->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('budget_goals', 0);
    }

    public function testUserCannotUpdateAnotherUsersCreditCard(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $creditCard = CreditCard::factory()->create([
            'user_id' => $owner->id,
            'name' => 'Owner Card',
        ]);

        $this->actingAs($intruder)
            ->put("/credit-card/{$creditCard->id}", [
                'name' => 'Intruder Card',
                'digits' => '1111',
                'due_date' => '10',
                'closing_date' => '05',
                'is_active' => '1',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('credit_cards', [
            'id' => $creditCard->id,
            'name' => 'Owner Card',
        ]);
    }

    public function testUserCannotViewAnotherUsersInvoice(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $creditCard = CreditCard::factory()->create([
            'user_id' => $owner->id,
        ]);
        $invoice = CreditCardInvoice::factory()->create([
            'credit_card_id' => $creditCard->id,
            'budget_id' => null,
        ]);

        $this->actingAs($intruder)
            ->get("/credit-card/invoice/{$invoice->id}")
            ->assertForbidden();
    }

    public function testUserCannotUpdateAnotherUsersPrepaidCard(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $prepaidCard = PrepaidCard::factory()->create([
            'user_id' => $owner->id,
            'name' => 'Owner Prepaid',
        ]);

        $this->actingAs($intruder)
            ->put("/prepaid-card/{$prepaidCard->id}", [
                'name' => 'Intruder Prepaid',
                'digits' => '1111',
                'is_active' => '1',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('prepaid_cards', [
            'id' => $prepaidCard->id,
            'name' => 'Owner Prepaid',
        ]);
    }

    public function testUserCannotViewAnotherUsersExtract(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $prepaidCard = PrepaidCard::factory()->create([
            'user_id' => $owner->id,
        ]);
        $extract = PrepaidCardExtract::factory()->create([
            'prepaid_card_id' => $prepaidCard->id,
            'budget_id' => null,
        ]);

        $this->actingAs($intruder)
            ->get("/prepaid-card/extract/{$extract->id}")
            ->assertForbidden();
    }

    public function testUserCannotDeleteAnotherUsersTag(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $tag = Tag::query()->create([
            'name' => 'PRIVATE',
            'user_id' => $owner->id,
        ]);

        $this->actingAs($intruder)
            ->delete("/tag/{$tag->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'PRIVATE',
        ]);
    }

    public function testTagSearchReturnsOnlyOwnedAndSharedTags(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        Tag::query()->create([
            'name' => 'OWN TRAVEL',
            'user_id' => $owner->id,
        ]);
        Tag::query()->create([
            'name' => 'SHARED TRAVEL',
            'user_id' => null,
        ]);
        Tag::query()->create([
            'name' => 'OTHER TRAVEL',
            'user_id' => $otherUser->id,
        ]);

        $this->actingAs($owner)
            ->get('/tag/search/TRAVEL')
            ->assertOk()
            ->assertJsonFragment(['name' => 'OWN TRAVEL'])
            ->assertJsonFragment(['name' => 'SHARED TRAVEL'])
            ->assertJsonMissing(['name' => 'OTHER TRAVEL']);
    }

    public function testUserCannotCreateExpenseForAnotherUsersInvoice(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $creditCard = CreditCard::factory()->create(['user_id' => $owner->id]);
        $invoice = CreditCardInvoice::factory()->create([
            'credit_card_id' => $creditCard->id,
            'budget_id' => null,
        ]);

        $this->actingAs($intruder)
            ->post('/credit-card/invoice/expense', [
                'credit_card_id' => $creditCard->id,
                'invoice_id' => $invoice->id,
                'description' => 'Mall',
                'date' => '2026-05-10',
                'value' => 100,
                'group' => 'WEEK_1',
                'tags' => [],
                'divisions' => [],
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('credit_card_invoice_expenses', 0);
    }

    public function testUserCannotCreateExpenseForAnotherUsersExtract(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $prepaidCard = PrepaidCard::factory()->create(['user_id' => $owner->id]);
        $extract = PrepaidCardExtract::factory()->create([
            'prepaid_card_id' => $prepaidCard->id,
            'budget_id' => null,
        ]);

        $this->actingAs($intruder)
            ->post('/prepaid-card/extract/expense', [
                'prepaid_card_id' => $prepaidCard->id,
                'extract_id' => $extract->id,
                'description' => 'Fuel',
                'date' => '2026-05-10',
                'value' => 80,
                'group' => 'WEEK_1',
                'tags' => [],
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('prepaid_card_extract_expenses', 0);
    }

    public function testInvoiceExpenseSearchReturnsOnlyOwnedDescriptions(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownerCard = CreditCard::factory()->create(['user_id' => $owner->id]);
        $ownerInvoice = CreditCardInvoice::factory()->create([
            'credit_card_id' => $ownerCard->id,
            'budget_id' => null,
        ]);
        CreditCardInvoiceExpense::query()->create([
            'description' => 'OWN COFFEE',
            'date' => '2026-05-10',
            'value' => 10,
            'group' => 'WEEK_1',
            'invoice_id' => $ownerInvoice->id,
        ]);

        $otherCard = CreditCard::factory()->create(['user_id' => $otherUser->id]);
        $otherInvoice = CreditCardInvoice::factory()->create([
            'credit_card_id' => $otherCard->id,
            'budget_id' => null,
        ]);
        CreditCardInvoiceExpense::query()->create([
            'description' => 'OTHER COFFEE',
            'date' => '2026-05-10',
            'value' => 10,
            'group' => 'WEEK_1',
            'invoice_id' => $otherInvoice->id,
        ]);

        $this->actingAs($owner)
            ->get('/credit-card/invoice/expense/search/COFFEE')
            ->assertOk()
            ->assertJsonFragment(['description' => 'OWN COFFEE'])
            ->assertJsonMissing(['description' => 'OTHER COFFEE']);
    }

    public function testExtractExpenseSearchReturnsOnlyOwnedDescriptions(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownerCard = PrepaidCard::factory()->create(['user_id' => $owner->id]);
        $ownerExtract = PrepaidCardExtract::factory()->create([
            'prepaid_card_id' => $ownerCard->id,
            'budget_id' => null,
        ]);
        PrepaidCardExtractExpense::query()->create([
            'description' => 'OWN FUEL',
            'date' => '2026-05-10',
            'value' => 10,
            'group' => 'WEEK_1',
            'extract_id' => $ownerExtract->id,
        ]);

        $otherCard = PrepaidCard::factory()->create(['user_id' => $otherUser->id]);
        $otherExtract = PrepaidCardExtract::factory()->create([
            'prepaid_card_id' => $otherCard->id,
            'budget_id' => null,
        ]);
        PrepaidCardExtractExpense::query()->create([
            'description' => 'OTHER FUEL',
            'date' => '2026-05-10',
            'value' => 10,
            'group' => 'WEEK_1',
            'extract_id' => $otherExtract->id,
        ]);

        $this->actingAs($owner)
            ->get('/prepaid-card/extract/expense/search/FUEL')
            ->assertOk()
            ->assertJsonFragment(['description' => 'OWN FUEL'])
            ->assertJsonMissing(['description' => 'OTHER FUEL']);
    }

    public function testBudgetProvisionSearchReturnsOnlyOwnedDescriptions(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownerBudget = Budget::factory()->create([
            'user_id' => $owner->id,
            'year' => '2026',
            'month' => '05',
        ]);
        BudgetProvision::query()->create([
            'description' => 'OWN RESERVE',
            'value' => 100,
            'group' => 'MONTHLY',
            'budget_id' => $ownerBudget->id,
        ]);

        $otherBudget = Budget::factory()->create([
            'user_id' => $otherUser->id,
            'year' => '2026',
            'month' => '05',
        ]);
        BudgetProvision::query()->create([
            'description' => 'OTHER RESERVE',
            'value' => 100,
            'group' => 'MONTHLY',
            'budget_id' => $otherBudget->id,
        ]);

        $this->actingAs($owner)
            ->get('/budget-provision/search/RESERVE')
            ->assertOk()
            ->assertJsonFragment(['description' => 'OWN RESERVE'])
            ->assertJsonMissing(['description' => 'OTHER RESERVE']);
    }
}
