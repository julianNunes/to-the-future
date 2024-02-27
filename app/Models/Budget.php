<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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

    /**
     * The accessors to append to the model's array form.
     * @var array
     */
    protected $appends = ['year_month'];

    /**
     * Accessors
     */
    protected function yearMonth(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes['month'] . '/' . $attributes['year'],

        );
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(BudgetExpense::class, 'budget_id', 'id');
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(BudgetIncome::class, 'budget_id', 'id');
    }

    public function goals(): HasMany
    {
        return $this->hasMany(BudgetGoal::class, 'budget_id', 'id');
    }

    public function provisions(): HasMany
    {
        return $this->hasMany(BudgetProvision::class, 'budget_id', 'id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(CreditCardInvoice::class, 'budget_id', 'id');
    }
}
