<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Models\BudgetExpense
 *
 * @property int $id
 * @property string $description Descrição
 * @property string $date Data de Vencimento
 * @property string $value
 * @property string|null $remarks
 * @property string|null $share_value Valor total compartilhado
 * @property int $paid
 * @property int $budget_id
 * @property int|null $share_user_id Id do usuario que sera compartilhado o gasto
 * @property int|null $financing_installment_id Id da parcela de financiamento
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Budget $budget
 * @property-read \App\Models\FinancingInstallment|null $financingInstallment
 * @property-read \App\Models\User|null $shareUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag> $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetExpense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetExpense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetExpense query()
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetExpense whereBudgetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetExpense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetExpense whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetExpense whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetExpense whereFinancingInstallmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetExpense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetExpense wherePaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetExpense whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetExpense whereShareUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetExpense whereShareValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetExpense whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetExpense whereValue($value)
 * @mixin \Eloquent
 */
class BudgetExpense extends Model
{
    use HasFactory;

    protected $table = 'budget_expenses';

    protected $fillable = [
        'description',
        'date',
        'value',
        'remarks',
        'paid',
        'share_value',
        'share_user_id',
        'budget_id',
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
