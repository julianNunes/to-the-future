<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

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
