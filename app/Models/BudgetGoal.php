<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Models\BudgetGoal
 *
 * @property int $id
 * @property string $description Descrição
 * @property string $value Valor da meta
 * @property string|null $group
 * @property int $count_only_share
 * @property int $budget_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Budget $budget
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag> $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetGoal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetGoal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetGoal query()
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetGoal whereBudgetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetGoal whereCountOnlyShare($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetGoal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetGoal whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetGoal whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetGoal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetGoal whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetGoal whereValue($value)
 * @mixin \Eloquent
 */
class BudgetGoal extends Model
{
    use HasFactory;

    protected $table = 'buget_goals';

    protected $fillable = [
        'description',
        'value',
        'group',
        'count_only_share',
        'budget_id'
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class, 'budget_id', 'id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
