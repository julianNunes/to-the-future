<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\CreditCardInvoice
 *
 * @property int $id
 * @property string $due_date Data de vencimento
 * @property string $closing_date Data de fechamento
 * @property string $year Ano da fatura
 * @property string $month Mês de vencimento
 * @property string $total
 * @property string|null $total_paid
 * @property int $closed Fatura fechada
 * @property string|null $remarks Observações
 * @property int $credit_card_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CreditCard $creditCard
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditCardInvoiceExpense> $expenses
 * @property-read int|null $expenses_count
 * @property-read \App\Models\CreditCardInvoiceFile|null $file
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoice whereClosed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoice whereClosingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoice whereCreditCardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoice whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoice whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoice whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoice whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoice whereTotalPaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoice whereYear($value)
 * @mixin \Eloquent
 */
class CreditCardInvoice extends Model
{
    use HasFactory;

    protected $table = 'credit_card_invoices';

    protected $fillable = [
        'due_date',
        'closing_date',
        'year',
        'month',
        'total',
        'total_paid',
        'closed',
        'remarks',
        'credit_card_id',
    ];

    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(CreditCard::class, 'credit_card_id', 'id');
    }

    public function file(): HasOne
    {
        return $this->hasOne(CreditCardInvoiceFile::class, 'invoice_id', 'id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(CreditCardInvoiceExpense::class, 'invoice_id', 'id');
    }
}
