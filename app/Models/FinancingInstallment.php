<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\FinancingInstallment
 *
 * @property int $id
 * @property string $value Valor da parcela
 * @property string|null $paid_value Valor pago da parcela
 * @property int $portion Parcela atual
 * @property string $date Data da vencimento
 * @property string|null $payment_date Data de Pagamento
 * @property int $paid
 * @property int $financing_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BudgetExpense|null $budgetExpense
 * @property-read \App\Models\Financing $financing
 * @method static \Illuminate\Database\Eloquent\Builder|FinancingInstallment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FinancingInstallment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FinancingInstallment query()
 * @method static \Illuminate\Database\Eloquent\Builder|FinancingInstallment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancingInstallment whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancingInstallment whereFinancingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancingInstallment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancingInstallment wherePaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancingInstallment wherePaidValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancingInstallment wherePaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancingInstallment wherePortion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancingInstallment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FinancingInstallment whereValue($value)
 * @mixin \Eloquent
 */
class FinancingInstallment extends Model
{
    use HasFactory;

    protected $table = 'financing_installments';

    protected $fillable = [
        'value',
        'paid_value',
        'portion',
        'date',
        'payment_date',
        'paid',
        'financing_id',
    ];

    public function financing(): BelongsTo
    {
        return $this->belongsTo(Financing::class, 'financing_id', 'id');
    }

    public function budgetExpense(): HasOne
    {
        return $this->hasOne(BudgetExpense::class, 'financing_installment_id', 'id');
    }
}
