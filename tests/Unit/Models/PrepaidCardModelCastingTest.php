<?php

namespace Tests\Unit\Models;

use App\Models\PrepaidCard;
use App\Models\PrepaidCardExtract;
use App\Models\PrepaidCardExtractExpense;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PrepaidCardModelCastingTest extends TestCase
{
    public function testPrepaidCardModelsExposeExpectedCastsAndRelations(): void
    {
        $card = new PrepaidCard();
        $card->forceFill([
            'is_active' => 1,
            'created_at' => '2026-05-16 10:20:30',
        ]);

        $extract = new PrepaidCardExtract();
        $extract->forceFill([
            'year' => '2026',
            'month' => '05',
            'credit' => '1234.56',
            'credit_date' => '2026-05-07',
            'created_at' => '2026-05-16 11:20:30',
        ]);

        $expense = new PrepaidCardExtractExpense();
        $expense->forceFill([
            'date' => '2026-05-12',
            'value' => '333.7',
            'group' => 'WEEK_2',
            'share_value' => '111.2',
            'created_at' => '2026-05-16 12:20:30',
        ]);

        $this->assertInstanceOf(BelongsTo::class, $card->user());
        $this->assertTrue($card->is_active);
        $this->assertInstanceOf(Carbon::class, $card->created_at);

        $this->assertInstanceOf(BelongsTo::class, $extract->prepaidCard());
        $this->assertSame('2026', $extract->year);
        $this->assertSame('05', $extract->month);
        $this->assertSame('1234.56', $extract->credit);
        $this->assertSame('2026-05-07', $extract->credit_date?->toDateString());
        $this->assertSame('05/2026', $extract->year_month);
        $this->assertInstanceOf(Carbon::class, $extract->created_at);

        $this->assertInstanceOf(BelongsTo::class, $expense->extract());
        $this->assertInstanceOf(BelongsTo::class, $expense->shareUser());
        $this->assertSame('2026-05-12', $expense->date?->toDateString());
        $this->assertSame('333.70', $expense->value);
        $this->assertSame('WEEK_2', $expense->group);
        $this->assertSame('111.20', $expense->share_value);
        $this->assertInstanceOf(Carbon::class, $expense->created_at);
    }
}
