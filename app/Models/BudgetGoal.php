<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class BudgetGoal extends Model
{
    use HasFactory;

    protected $table = 'buget_goals';

    protected $fillable = [
        'description',
        'value',
        'group',
        'count_share',
        'budget_id'
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class, 'budget_id', 'id');
    }

    public function tag(): MorphOne
    {
        return $this->morphOne(Tag::class, 'taggable');
    }
}
