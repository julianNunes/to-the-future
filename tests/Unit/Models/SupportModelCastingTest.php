<?php

namespace Tests\Unit\Models;

use App\Models\Financing;
use App\Models\FinancingInstallment;
use App\Models\FixExpense;
use App\Models\Provision;
use App\Models\ShareUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SupportModelCastingTest extends TestCase
{
    public function testSupportModelsExposeExpectedCastsAndBelongsToRelations(): void
    {
        $financing = new Financing();
        $financing->forceFill([
            'start_date' => '2026-05-15',
            'total' => '1200.5',
            'fees_monthly' => '1.75',
            'portion_total' => '3',
            'created_at' => '2026-05-16 10:20:30',
        ]);

        $installment = new FinancingInstallment();
        $installment->forceFill([
            'value' => '450.9',
            'paid_value' => '450.9',
            'portion' => '1',
            'date' => '2026-06-01',
            'payment_date' => '2026-06-02',
            'paid' => 1,
            'created_at' => '2026-05-16 11:20:30',
        ]);

        $expense = new FixExpense();
        $expense->forceFill([
            'due_date' => '05',
            'value' => '150.5',
            'share_value' => '50',
            'created_at' => '2026-05-16 12:20:30',
        ]);

        $provision = new Provision();
        $provision->forceFill([
            'value' => '100',
            'group' => 'MONTHLY',
            'share_value' => '40',
            'created_at' => '2026-05-16 13:20:30',
        ]);

        $shareUser = new ShareUser();
        $shareUser->forceFill([
            'user_id' => '10',
            'share_user_id' => '20',
            'created_at' => '2026-05-16 14:20:30',
        ]);

        $this->assertInstanceOf(BelongsTo::class, $financing->user());
        $this->assertSame('2026-05-15', $financing->start_date?->toDateString());
        $this->assertSame('1200.50', $financing->total);
        $this->assertSame('1.75', $financing->fees_monthly);
        $this->assertSame(3, $financing->portion_total);
        $this->assertInstanceOf(Carbon::class, $financing->created_at);

        $this->assertInstanceOf(BelongsTo::class, $installment->financing());
        $this->assertSame('450.90', $installment->value);
        $this->assertSame('450.90', $installment->paid_value);
        $this->assertSame(1, $installment->portion);
        $this->assertSame('2026-06-01', $installment->date?->toDateString());
        $this->assertSame('2026-06-02', $installment->payment_date?->toDateString());
        $this->assertTrue($installment->paid);
        $this->assertInstanceOf(Carbon::class, $installment->created_at);

        $this->assertInstanceOf(BelongsTo::class, $expense->user());
        $this->assertInstanceOf(BelongsTo::class, $expense->shareUser());
        $this->assertSame('05', $expense->due_date);
        $this->assertSame('150.50', $expense->value);
        $this->assertSame('50.00', $expense->share_value);
        $this->assertInstanceOf(Carbon::class, $expense->created_at);

        $this->assertInstanceOf(BelongsTo::class, $provision->user());
        $this->assertInstanceOf(BelongsTo::class, $provision->shareUser());
        $this->assertSame('100.00', $provision->value);
        $this->assertSame('MONTHLY', $provision->group);
        $this->assertSame('40.00', $provision->share_value);
        $this->assertInstanceOf(Carbon::class, $provision->created_at);

        $this->assertInstanceOf(BelongsTo::class, $shareUser->user());
        $this->assertInstanceOf(BelongsTo::class, $shareUser->shareUser());
        $this->assertSame(10, $shareUser->user_id);
        $this->assertSame(20, $shareUser->share_user_id);
        $this->assertInstanceOf(Carbon::class, $shareUser->created_at);
    }
}
