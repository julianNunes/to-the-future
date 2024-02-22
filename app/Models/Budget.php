<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Budget
 *
 * @property int $id
 * @property string $year Ano da fatura
 * @property string $month Mês de vencimento
 * @property string $total_expense Total de Despesas
 * @property string $total_income Total da Receita
 * @property int $closed Marcador para finalizar o Orçamento
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BudgetExpense> $expenses
 * @property-read int|null $expenses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BudgetGoal> $goals
 * @property-read int|null $goals_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BudgetIncome> $incomes
 * @property-read int|null $incomes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BudgetProvision> $provisions
 * @property-read int|null $provisions_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Budget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Budget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Budget query()
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereClosed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereTotalExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereTotalIncome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereYear($value)
 * @mixin \Eloquent
 */
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
}
