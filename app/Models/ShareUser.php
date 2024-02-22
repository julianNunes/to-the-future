<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\ShareUser
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $share_user_id Id do usuario que sera compartilhado o gasto
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $shareUser
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|ShareUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShareUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShareUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShareUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShareUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShareUser whereShareUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShareUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShareUser whereUserId($value)
 * @mixin \Eloquent
 */
class ShareUser extends Model
{
    use HasFactory;

    protected $table = 'share_users';

    protected $fillable = [
        'user_id',
        'share_user_id',
    ];

    public function shareUser(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'share_user_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
