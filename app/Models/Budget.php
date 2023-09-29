<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Budget extends Model
{
    use HasFactory;

    protected $table = 'budgets';

    protected $fillable = [
        'year',
        'month',
        'total_expense',
        'total_income',
        'closed',
        'user_id',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(BudgetEntry::class, 'budget_id', 'id');
    }

    public function provisions(): HasMany
    {
        return $this->hasMany(BudgetProvision::class, 'budget_id', 'id');
    }
}
