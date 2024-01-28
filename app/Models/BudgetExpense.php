<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class BudgetExpense extends Model
{
    use HasFactory;

    protected $table = 'budget_expenses';

    protected $fillable = [
        'description',
        'date',
        'value',
        'remarks',
        'share_value',
        'budget_id',
        'share_user_id',
        'financing_installment_id',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class, 'budget_id', 'id');
    }

    public function financingInstallment(): HasOne
    {
        return $this->hasOne(FinancingInstallment::class, 'id', 'financing_installment_id');
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
