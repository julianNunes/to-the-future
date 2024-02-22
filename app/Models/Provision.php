<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Models\Provision
 *
 * @property int $id
 * @property string $description Descricao
 * @property string $value
 * @property string $group
 * @property string|null $remarks Observacoes
 * @property string|null $share_value Valor total compartilhado
 * @property int|null $share_user_id Id do usuario que sera compartilhado o gasto
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $shareUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag> $tags
 * @property-read int|null $tags_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Provision newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Provision newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Provision query()
 * @method static \Illuminate\Database\Eloquent\Builder|Provision whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Provision whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Provision whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Provision whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Provision whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Provision whereShareUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Provision whereShareValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Provision whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Provision whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Provision whereValue($value)
 * @mixin \Eloquent
 */
class Provision extends Model
{
    use HasFactory;

    protected $table = 'provisions';

    protected $fillable = [
        'description',
        'value',
        'group',
        'remarks',
        'share_value',
        'share_user_id',
        'user_id'
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
