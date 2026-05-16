<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * @mixin IdeHelperPrepaidCard
 */
class PrepaidCard extends Model
{
    use HasFactory;

    protected $table = 'prepaid_cards';

    protected $fillable = [
        'name',
        'digits',
        'is_active',
        'user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function extracts(): HasMany
    {
        return $this->hasMany(PrepaidCardExtract::class, 'prepaid_card_id', 'id');
    }
}
