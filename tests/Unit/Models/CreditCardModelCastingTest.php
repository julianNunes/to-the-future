<?php

namespace Tests\Unit\Models;

use App\Models\CreditCard;
use App\Models\CreditCardInvoice;
use App\Models\CreditCardInvoiceExpense;
use App\Models\CreditCardInvoiceExpenseDivision;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CreditCardModelCastingTest extends TestCase
{
    public function testCreditCardModelsExposeExpectedCastsAndRelations(): void
    {
        $card = new CreditCard();
        $card->forceFill([
            'due_date' => '10',
            'closing_date' => '05',
            'is_active' => 1,
            'created_at' => '2026-05-16 10:20:30',
        ]);

        $invoice = new CreditCardInvoice();
        $invoice->forceFill([
            'due_date' => '2026-05-10',
            'closing_date' => '2026-05-05',
            'year' => '2026',
            'month' => '05',
            'total' => '1200.5',
            'total_paid' => '100.1',
            'closed' => 0,
            'created_at' => '2026-05-16 11:20:30',
        ]);

        $expense = new CreditCardInvoiceExpense();
        $expense->forceFill([
            'date' => '2026-05-12',
            'value' => '333.7',
            'group' => 'WEEK_2',
            'group_portion' => '00000000-0000-0000-0000-000000000456',
            'portion' => '2',
            'portion_total' => '10',
            'share_value' => '111.2',
            'created_at' => '2026-05-16 12:20:30',
        ]);

        $division = new CreditCardInvoiceExpenseDivision();
        $division->forceFill([
            'value' => '111.2',
            'share_value' => '55.1',
            'created_at' => '2026-05-16 13:20:30',
        ]);

        $this->assertInstanceOf(BelongsTo::class, $card->user());
        $this->assertSame('10', $card->due_date);
        $this->assertSame('05', $card->closing_date);
        $this->assertTrue($card->is_active);
        $this->assertInstanceOf(Carbon::class, $card->created_at);

        $this->assertInstanceOf(BelongsTo::class, $invoice->creditCard());
        $this->assertSame('2026-05-10', $invoice->due_date?->toDateString());
        $this->assertSame('2026-05-05', $invoice->closing_date?->toDateString());
        $this->assertSame('2026', $invoice->year);
        $this->assertSame('05', $invoice->month);
        $this->assertSame('1200.50', $invoice->total);
        $this->assertSame('100.10', $invoice->total_paid);
        $this->assertFalse($invoice->closed);
        $this->assertInstanceOf(Carbon::class, $invoice->created_at);

        $this->assertInstanceOf(BelongsTo::class, $expense->invoice());
        $this->assertInstanceOf(BelongsTo::class, $expense->shareUser());
        $this->assertSame('2026-05-12', $expense->date?->toDateString());
        $this->assertSame('333.70', $expense->value);
        $this->assertSame('WEEK_2', $expense->group);
        $this->assertSame('00000000-0000-0000-0000-000000000456', $expense->group_portion);
        $this->assertSame(2, $expense->portion);
        $this->assertSame(10, $expense->portion_total);
        $this->assertSame('111.20', $expense->share_value);
        $this->assertInstanceOf(Carbon::class, $expense->created_at);

        $this->assertInstanceOf(BelongsTo::class, $division->expense());
        $this->assertInstanceOf(BelongsTo::class, $division->shareUser());
        $this->assertSame('111.20', $division->value);
        $this->assertSame('55.10', $division->share_value);
        $this->assertInstanceOf(Carbon::class, $division->created_at);
    }
}
