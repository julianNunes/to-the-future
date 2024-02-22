<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Models\CreditCardInvoiceExpense
 *
 * @property int $id
 * @property string $description Descricao
 * @property string $date Data da compra
 * @property string $value
 * @property string $group
 * @property int|null $portion Parcela atual
 * @property int|null $portion_total Total de Parcelas
 * @property string|null $remarks
 * @property string|null $share_value Valor total compartilhado
 * @property int $invoice_id
 * @property int|null $share_user_id Id do usuario que sera compartilhado o gasto
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditCardInvoiceExpenseDivision> $divisions
 * @property-read int|null $divisions_count
 * @property-read \App\Models\CreditCardInvoice $invoice
 * @property-read \App\Models\User|null $shareUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag> $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpense query()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpense whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpense whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpense whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpense whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpense wherePortion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpense wherePortionTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpense whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpense whereShareUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpense whereShareValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpense whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpense whereValue($value)
 * @mixin \Eloquent
 */
class CreditCardInvoiceExpense extends Model
{
    use HasFactory;

    protected $table = 'credit_card_invoice_expenses';

    protected $fillable = [
        'description',
        'date',
        'value',
        'group',
        'portion',
        'portion_total',
        'remarks',
        'share_value',
        'invoice_id',
        'share_user_id',
    ];

    public function shareUser(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'share_user_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(CreditCardInvoice::class, 'invoice_id', 'id');
    }

    public function divisions(): HasMany
    {
        return $this->hasMany(CreditCardInvoiceExpenseDivision::class, 'expense_id', 'id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
