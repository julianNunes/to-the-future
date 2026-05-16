<?php

namespace Tests\Unit\Models;

use App\Models\Budget;
use App\Models\BudgetExpense;
use App\Models\BudgetIncome;
use App\Models\BudgetProvision;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BudgetModelCastingTest extends TestCase
{
    public function testBudgetModelsExposeExpectedCastsAndRelations(): void
    {
        $budget = new Budget();
        $budget->forceFill([
            'year' => 2026,
            'month' => 5,
            'start_week_1' => '2026-05-01',
            'end_week_1' => '2026-05-07',
            'total_expense' => '123.4',
            'total_income' => '456.7',
            'closed' => 1,
            'created_at' => '2026-05-16 10:20:30',
        ]);

        $expense = new BudgetExpense();
        $expense->forceFill([
            'date' => '2026-05-12',
            'value' => '222.4',
            'group' => 'INDIVIDUAL',
            'group_portion' => '00000000-0000-0000-0000-000000000123',
            'portion' => '2',
            'portion_total' => '10',
            'paid' => 1,
            'share_value' => '111.2',
            'created_at' => '2026-05-16 11:20:30',
        ]);

        $income = new BudgetIncome();
        $income->forceFill([
            'date' => '2026-05-06',
            'value' => '3200.45',
            'created_at' => '2026-05-16 12:20:30',
        ]);

        $provision = new BudgetProvision();
        $provision->forceFill([
            'value' => '410.7',
            'group' => 'WEEK_2',
            'share_value' => '205.35',
            'created_at' => '2026-05-16 13:20:30',
        ]);

        $this->assertInstanceOf(BelongsTo::class, $budget->user());
        $this->assertSame('2026', $budget->year);
        $this->assertSame('5', $budget->month);
        $this->assertSame('2026-05-01', $budget->start_week_1?->toDateString());
        $this->assertSame('2026-05-07', $budget->end_week_1?->toDateString());
        $this->assertSame('123.40', $budget->total_expense);
        $this->assertSame('456.70', $budget->total_income);
        $this->assertTrue($budget->closed);
        $this->assertSame('5/2026', $budget->year_month);
        $this->assertInstanceOf(Carbon::class, $budget->created_at);

        $this->assertInstanceOf(BelongsTo::class, $expense->budget());
        $this->assertInstanceOf(BelongsTo::class, $expense->shareUser());
        $this->assertSame('2026-05-12', $expense->date?->toDateString());
        $this->assertSame('222.40', $expense->value);
        $this->assertSame('INDIVIDUAL', $expense->group);
        $this->assertSame('00000000-0000-0000-0000-000000000123', $expense->group_portion);
        $this->assertSame(2, $expense->portion);
        $this->assertSame(10, $expense->portion_total);
        $this->assertTrue($expense->paid);
        $this->assertSame('111.20', $expense->share_value);
        $this->assertInstanceOf(Carbon::class, $expense->created_at);

        $this->assertInstanceOf(BelongsTo::class, $income->budget());
        $this->assertSame('2026-05-06', $income->date?->toDateString());
        $this->assertSame('3200.45', $income->value);
        $this->assertInstanceOf(Carbon::class, $income->created_at);

        $this->assertInstanceOf(BelongsTo::class, $provision->budget());
        $this->assertInstanceOf(BelongsTo::class, $provision->shareUser());
        $this->assertSame('410.70', $provision->value);
        $this->assertSame('WEEK_2', $provision->group);
        $this->assertSame('205.35', $provision->share_value);
        $this->assertInstanceOf(Carbon::class, $provision->created_at);
    }
}
