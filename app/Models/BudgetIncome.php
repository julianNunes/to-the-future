<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Models\BudgetIncome
 *
 * @property int $id
 * @property string $description Descrição
 * @property string $date Data
 * @property string $value
 * @property string|null $remarks
 * @property int $budget_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Budget $budget
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag> $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetIncome newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetIncome newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetIncome query()
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetIncome whereBudgetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetIncome whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetIncome whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetIncome whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetIncome whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetIncome whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetIncome whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetIncome whereValue($value)
 * @mixin \Eloquent
 */
class BudgetIncome extends Model
{
    use HasFactory;

    protected $table = 'bugdet_incomes';

    protected $fillable = [
        'description',
        'date',
        'value',
        'remarks',
        'budget_id',
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
