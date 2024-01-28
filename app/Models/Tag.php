<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    use HasFactory;

    protected $table = 'tags';

    protected $fillable = [
        'name',
        'user_id'
    ];

    public function budgetExpenses(): MorphToMany
    {
        return $this->morphedByMany(BudgetExpense::class, 'taggable');
    }

    public function expenses(): MorphToMany
    {
        return $this->morphedByMany(CreditCardInvoiceExpense::class, 'taggable');
    }

    public function expenseDivisions(): MorphToMany
    {
        return $this->morphedByMany(CreditCardInvoiceExpenseDivision::class, 'taggable');
    }
}
