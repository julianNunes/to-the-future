<?php

namespace Tests\Unit\Helpers;

use App\Helpers\Budget\BudgetShowRelations;
use Tests\TestCase;

class BudgetShowRelationsTest extends TestCase
{
    public function testItCentralizesBudgetShowEagerLoads(): void
    {
        $relations = (new BudgetShowRelations())->relations();

        $this->assertArrayHasKey('expenses', $relations);
        $this->assertContains('incomes.tags', $relations);
        $this->assertArrayHasKey('invoices', $relations);
        $this->assertArrayHasKey('extracts', $relations);
        $this->assertSame(['tags', 'shareUser'], $relations['expenses']);
        $this->assertSame(['tags', 'shareUser'], $relations['invoices']['expenses']['divisions']);
        $this->assertSame(['tags', 'shareUser'], $relations['extracts']['expenses']);
    }
}
