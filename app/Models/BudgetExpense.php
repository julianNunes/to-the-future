<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, MorphToMany};

/**
 * @mixin IdeHelperBudgetExpense
 */
class BudgetExpense extends Model
{
    use HasFactory;

    protected $table = 'budget_expenses';

    protected $fillable = [
        'description',
        'date',
        'value',
        'group',
        'group_portion',
        'portion',
        'portion_total',
        'remarks',
        'paid',
        'share_value',
        'share_user_id',
        'budget_id',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'value' => 'decimal:2',
        'group' => 'string',
        'group_portion' => 'string',
        'portion' => 'integer',
        'portion_total' => 'integer',
        'paid' => 'boolean',
        'share_value' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class, 'budget_id', 'id');
    }

    public function shareUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'share_user_id', 'id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
