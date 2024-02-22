<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Models\FixExpense
 *
 * @property int $id
 * @property string $description Descrição
 * @property string $due_date Dia de Vencimento
 * @property string $value
 * @property string|null $remarks
 * @property string|null $share_value Valor Total compartilhado
 * @property int|null $share_user_id Id do usuario que sera compartilhado o gasto
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $shareUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag> $tags
 * @property-read int|null $tags_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|FixExpense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FixExpense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FixExpense query()
 * @method static \Illuminate\Database\Eloquent\Builder|FixExpense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixExpense whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixExpense whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixExpense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixExpense whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixExpense whereShareUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixExpense whereShareValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixExpense whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixExpense whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FixExpense whereValue($value)
 * @mixin \Eloquent
 */
class FixExpense extends Model
{
    use HasFactory;

    protected $table = 'fix_expenses';

    protected $fillable = [
        'description',
        'due_date',
        'value',
        'remarks',
        'share_value',
        'share_user_id',
        'user_id',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function shareUser(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'share_user_id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
