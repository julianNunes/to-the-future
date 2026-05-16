<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @mixin IdeHelperBudgetIncome
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

    protected $casts = [
        'date' => 'date:Y-m-d',
        'value' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
