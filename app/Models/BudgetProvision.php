<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Models\BudgetProvision
 *
 * @property int $id
 * @property string $description Descricao
 * @property string $value
 * @property string $group
 * @property string|null $remarks Observacoes
 * @property string|null $share_value Valor total compartilhado
 * @property int|null $share_user_id Id do usuario que sera compartilhado o gasto
 * @property int $budget_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Budget $budget
 * @property-read \App\Models\User|null $shareUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag> $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetProvision newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetProvision newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetProvision query()
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetProvision whereBudgetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetProvision whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetProvision whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetProvision whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetProvision whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetProvision whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetProvision whereShareUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetProvision whereShareValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetProvision whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetProvision whereValue($value)
 * @mixin \Eloquent
 */
class BudgetProvision extends Model
{
    use HasFactory;

    protected $table = 'budget_provisions';

    protected $fillable = [
        'description',
        'value',
        'group',
        'remarks',
        'budget_id',
        'share_value',
        'share_user_id',
    ];

    public function shareUser(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'share_user_id');
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class, 'budget_id', 'id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
