<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @mixin IdeHelperBudgetProvision
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

    protected $casts = [
        'value' => 'decimal:2',
        'group' => 'string',
        'share_value' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function shareUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'share_user_id', 'id');
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
