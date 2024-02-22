<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Models\Tag
 *
 * @property int $id
 * @property string $name
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BudgetExpense> $budgetExpenses
 * @property-read int|null $budget_expenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditCardInvoiceExpenseDivision> $expenseDivisions
 * @property-read int|null $expense_divisions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditCardInvoiceExpense> $expenses
 * @property-read int|null $expenses_count
 * @method static \Illuminate\Database\Eloquent\Builder|Tag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag query()
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Tag whereUserId($value)
 * @mixin \Eloquent
 */
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
