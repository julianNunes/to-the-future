<?php

namespace Tests\Unit\Repositories;

use App\Models\Budget;
use App\Models\BudgetExpense;
use App\Models\Tag;
use App\Models\User;
use App\Repositories\TagRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TagRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function testSaveTagsToModelBatchLoadsExistingTagsAndCreatesMissingOnes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $existingTag = Tag::create([
            'name' => 'FOOD',
            'user_id' => $user->id,
        ]);

        $budget = Budget::factory()->create([
            'user_id' => $user->id,
            'year' => '2026',
            'month' => '05',
        ]);

        $expense = BudgetExpense::create([
            'description' => 'Dinner',
            'date' => '2026-05-10',
            'value' => 120,
            'group' => 'MONTHLY',
            'paid' => false,
            'share_value' => 0,
            'budget_id' => $budget->id,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        (new TagRepository())->saveTagsToModel($expense, collect([
            ['name' => 'FOOD'],
            ['name' => 'TRAVEL'],
            ['name' => 'FOOD'],
        ]));

        $tagBatchQueries = collect(DB::getQueryLog())
            ->filter(fn(array $query) => str_contains($query['query'], 'from `tags`') && str_contains($query['query'], 'where `name` in'));

        DB::disableQueryLog();

        $this->assertCount(1, $tagBatchQueries);
        $this->assertDatabaseHas('tags', ['name' => 'TRAVEL', 'user_id' => $user->id]);
        $this->assertEqualsCanonicalizing(
            [$existingTag->id, Tag::where('name', 'TRAVEL')->value('id')],
            $expense->tags()->pluck('tags.id')->all()
        );
    }
}
