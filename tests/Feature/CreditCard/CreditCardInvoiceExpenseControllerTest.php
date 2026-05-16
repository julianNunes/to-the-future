<?php

namespace Tests\Feature\CreditCard;

use App\Models\CreditCard;
use App\Models\CreditCardInvoice;
use App\Models\CreditCardInvoiceExpense;
use App\Models\CreditCardInvoiceExpenseDivision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditCardInvoiceExpenseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testInvoiceExpenseStoreRequiresCoreFields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/credit-card/invoice/1')
            ->post('/credit-card/invoice/expense', [
                'credit_card_id' => '',
                'invoice_id' => '',
                'description' => '',
                'date' => '',
                'value' => '',
                'group' => '',
            ])
            ->assertRedirect('/credit-card/invoice/1')
            ->assertSessionHasErrors(['credit_card_id', 'invoice_id', 'description', 'date', 'value', 'group']);

        $this->assertDatabaseCount('credit_card_invoice_expenses', 0);
    }

    public function testInvoiceExpenseStoreNormalizesAndPersistsPayload(): void
    {
        $user = User::factory()->create();
        $shareUser = User::factory()->create();
        $creditCard = CreditCard::factory()->create(['user_id' => $user->id]);
        $invoice = CreditCardInvoice::factory()->create([
            'credit_card_id' => $creditCard->id,
            'budget_id' => null,
            'year' => '2026',
            'month' => '05',
        ]);

        $this->actingAs($user)
            ->from("/credit-card/invoice/{$invoice->id}")
            ->post('/credit-card/invoice/expense', [
                'credit_card_id' => (string) $creditCard->id,
                'invoice_id' => (string) $invoice->id,
                'description' => '  Market Purchase  ',
                'date' => '2026-05-10',
                'value' => 'R$ 120,40',
                'group' => 'WEEK_1',
                'remarks' => '  Shared  ',
                'share_value' => 'R$ 60,20',
                'share_user_id' => (string) $shareUser->id,
                'tags' => [
                    ['name' => 'FOOD'],
                ],
                'divisions' => [
                    [
                        'description' => '  Grocery  ',
                        'value' => 'R$ 120,40',
                        'remarks' => '  Division  ',
                        'share_value' => 'R$ 60,20',
                        'share_user_id' => (string) $shareUser->id,
                        'tags' => [
                            ['name' => 'MARKET'],
                        ],
                    ],
                ],
            ])
            ->assertRedirect("/credit-card/invoice/{$invoice->id}");

        $expense = CreditCardInvoiceExpense::query()->with(['tags', 'divisions.tags'])->firstOrFail();

        $this->assertSame('Market Purchase', $expense->description);
        $this->assertSame('2026-05-10', $expense->date);
        $this->assertSame('WEEK_1', $expense->group);
        $this->assertSame('Shared', $expense->remarks);
        $this->assertSame($shareUser->id, $expense->share_user_id);
        $this->assertSame(['FOOD'], $expense->tags->pluck('name')->all());
        $this->assertEquals(120.40, (float) $expense->value);
        $this->assertEquals(60.20, (float) $expense->share_value);
        $this->assertCount(1, $expense->divisions);
        $this->assertSame('Grocery', $expense->divisions->first()->description);
        $this->assertSame(['MARKET'], $expense->divisions->first()->tags->pluck('name')->all());
    }

    public function testInvoiceExpenseStoreCreatesAllPortionsWhenRequested(): void
    {
        $user = User::factory()->create();
        $creditCard = CreditCard::factory()->create([
            'user_id' => $user->id,
            'due_date' => '05',
            'closing_date' => '25',
        ]);
        $invoice = CreditCardInvoice::factory()->create([
            'credit_card_id' => $creditCard->id,
            'budget_id' => null,
            'due_date' => '2026-05-05',
            'closing_date' => '2026-04-25',
            'year' => '2026',
            'month' => '05',
        ]);

        $this->actingAs($user)
            ->from("/credit-card/invoice/{$invoice->id}")
            ->post('/credit-card/invoice/expense', [
                'credit_card_id' => $creditCard->id,
                'invoice_id' => $invoice->id,
                'description' => 'Notebook',
                'date' => '2026-05-03',
                'value' => '500',
                'group' => 'PORTION',
                'portion' => '1',
                'portion_total' => '3',
                'tags' => [],
                'divisions' => [],
            ])
            ->assertRedirect("/credit-card/invoice/{$invoice->id}");

        $expenses = CreditCardInvoiceExpense::query()
            ->where('description', 'Notebook')
            ->orderBy('portion')
            ->get();

        $this->assertCount(3, $expenses);
        $this->assertSame([1, 2, 3], $expenses->pluck('portion')->map(fn ($item) => (int) $item)->all());
        $this->assertDatabaseHas('credit_card_invoices', [
            'credit_card_id' => $creditCard->id,
            'year' => '2026',
            'month' => '06',
        ]);
        $this->assertDatabaseHas('credit_card_invoices', [
            'credit_card_id' => $creditCard->id,
            'year' => '2026',
            'month' => '07',
        ]);
    }

    public function testInvoiceExpenseUpdateNormalizesAndPersistsPayload(): void
    {
        $user = User::factory()->create();
        $shareUser = User::factory()->create();
        $creditCard = CreditCard::factory()->create(['user_id' => $user->id]);
        $invoice = CreditCardInvoice::factory()->create([
            'credit_card_id' => $creditCard->id,
            'budget_id' => null,
        ]);
        $expense = CreditCardInvoiceExpense::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => 'Old Expense',
            'date' => '2026-05-10',
            'value' => 100,
            'group' => 'WEEK_1',
            'remarks' => null,
            'share_value' => null,
            'share_user_id' => null,
        ]);

        $this->actingAs($user)
            ->from("/credit-card/invoice/{$invoice->id}")
            ->put("/credit-card/invoice/expense/{$expense->id}", [
                'credit_card_id' => (string) $creditCard->id,
                'invoice_id' => (string) $invoice->id,
                'description' => '  Updated Expense  ',
                'date' => '2026-05-12',
                'value' => 'R$ 333,70',
                'group' => 'WEEK_2',
                'remarks' => '  Updated note  ',
                'share_value' => 'R$ 111,20',
                'share_user_id' => (string) $shareUser->id,
                'tags' => [
                    ['name' => 'UPDATED'],
                ],
                'divisions' => [
                    [
                        'description' => '  Part 1  ',
                        'value' => 'R$ 333,70',
                        'remarks' => '  Split  ',
                        'share_value' => 'R$ 111,20',
                        'share_user_id' => (string) $shareUser->id,
                        'tags' => [
                            ['name' => 'PART'],
                        ],
                    ],
                ],
            ])
            ->assertRedirect("/credit-card/invoice/{$invoice->id}");

        $expense->refresh()->load(['tags', 'divisions.tags']);

        $this->assertSame('Updated Expense', $expense->description);
        $this->assertSame('2026-05-12', $expense->date);
        $this->assertSame('WEEK_2', $expense->group);
        $this->assertSame('Updated note', $expense->remarks);
        $this->assertSame($shareUser->id, $expense->share_user_id);
        $this->assertSame(['UPDATED'], $expense->tags->pluck('name')->all());
        $this->assertEquals(333.70, (float) $expense->value);
        $this->assertEquals(111.20, (float) $expense->share_value);
        $this->assertCount(1, $expense->divisions);
        $this->assertSame('Part 1', $expense->divisions->first()->description);
        $this->assertSame(['PART'], $expense->divisions->first()->tags->pluck('name')->all());
    }

    public function testInvoiceExpenseImportNormalizesAndPersistsParsedRows(): void
    {
        $user = User::factory()->create();
        $shareUser = User::factory()->create();
        $creditCard = CreditCard::factory()->create(['user_id' => $user->id]);
        $invoice = CreditCardInvoice::factory()->create([
            'credit_card_id' => $creditCard->id,
            'budget_id' => null,
            'year' => '2026',
            'month' => '05',
        ]);

        $this->actingAs($user)
            ->from("/credit-card/invoice/{$invoice->id}")
            ->post('/credit-card/invoice/expense/import-excel', [
                'invoice_id' => (string) $invoice->id,
                'data' => [
                    [
                        'description' => '  Imported Expense  ',
                        'date' => '2026-05-09',
                        'value' => 'R$ 80,50',
                        'group' => 'WEEK_3',
                        'portion' => '',
                        'portion_total' => '',
                        'remarks' => '  Imported  ',
                        'share_value' => 'R$ 20,10',
                        'share_user_id' => (string) $shareUser->id,
                        'tags' => [
                            ['name' => 'IMPORT'],
                        ],
                    ],
                ],
            ])
            ->assertRedirect("/credit-card/invoice/{$invoice->id}");

        $expense = CreditCardInvoiceExpense::query()->with('tags')->firstOrFail();

        $this->assertSame('Imported Expense', $expense->description);
        $this->assertSame('2026-05-09', $expense->date);
        $this->assertSame('Imported', $expense->remarks);
        $this->assertSame('WEEK_3', $expense->group);
        $this->assertSame($shareUser->id, $expense->share_user_id);
        $this->assertSame(['IMPORT'], $expense->tags->pluck('name')->all());
        $this->assertEquals(80.50, (float) $expense->value);
        $this->assertEquals(20.10, (float) $expense->share_value);
    }

    public function testInvoiceExpenseDeleteRemovesExpenseAndDivisions(): void
    {
        $user = User::factory()->create();
        $creditCard = CreditCard::factory()->create(['user_id' => $user->id]);
        $invoice = CreditCardInvoice::factory()->create([
            'credit_card_id' => $creditCard->id,
            'budget_id' => null,
        ]);
        $expense = CreditCardInvoiceExpense::factory()->create([
            'invoice_id' => $invoice->id,
        ]);
        $division = CreditCardInvoiceExpenseDivision::factory()->create([
            'expense_id' => $expense->id,
        ]);

        $this->actingAs($user)
            ->from("/credit-card/invoice/{$invoice->id}")
            ->delete("/credit-card/invoice/expense/{$expense->id}")
            ->assertRedirect("/credit-card/invoice/{$invoice->id}");

        $this->assertDatabaseMissing('credit_card_invoice_expenses', ['id' => $expense->id]);
        $this->assertDatabaseMissing('credit_card_invoice_expense_divisions', ['id' => $division->id]);
    }

    public function testInvoiceExpenseDeletePortionsRemovesAllMatchingInstallments(): void
    {
        $user = User::factory()->create();
        $creditCard = CreditCard::factory()->create(['user_id' => $user->id]);
        $invoice = CreditCardInvoice::factory()->create([
            'credit_card_id' => $creditCard->id,
            'budget_id' => null,
        ]);
        $nextInvoice = CreditCardInvoice::factory()->create([
            'credit_card_id' => $creditCard->id,
            'budget_id' => null,
            'year' => '2026',
            'month' => '06',
        ]);

        $firstExpense = CreditCardInvoiceExpense::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => 'Notebook',
            'portion' => 1,
            'portion_total' => 3,
        ]);
        CreditCardInvoiceExpense::factory()->create([
            'invoice_id' => $nextInvoice->id,
            'description' => 'Notebook',
            'portion' => 2,
            'portion_total' => 3,
        ]);
        CreditCardInvoiceExpense::factory()->create([
            'invoice_id' => $nextInvoice->id,
            'description' => 'Notebook',
            'portion' => 3,
            'portion_total' => 3,
        ]);
        CreditCardInvoiceExpense::factory()->create([
            'invoice_id' => $invoice->id,
            'description' => 'Other expense',
            'portion' => null,
            'portion_total' => null,
        ]);

        $this->actingAs($user)
            ->from("/credit-card/invoice/{$invoice->id}")
            ->delete("/credit-card/invoice/expense/{$firstExpense->id}/delete-all-portions")
            ->assertRedirect("/credit-card/invoice/{$invoice->id}");

        $this->assertDatabaseCount('credit_card_invoice_expenses', 1);
        $this->assertDatabaseHas('credit_card_invoice_expenses', ['description' => 'Other expense']);
    }
}
