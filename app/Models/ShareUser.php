<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
 * @mixin IdeHelperShareUser
 */
class ShareUser extends Model
{
    use HasFactory;

    protected $table = 'share_users';

    protected $fillable = [
        'user_id',
        'share_user_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'share_user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function shareUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'share_user_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
