<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * App\Models\CreditCardInvoiceExpenseDivision
 *
 * @property int $id
 * @property string $description Descricao
 * @property string $value
 * @property string|null $remarks
 * @property string|null $share_value Valor total compartilhado
 * @property int $expense_id
 * @property int|null $share_user_id Id do usuario que sera compartilhado o gasto
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CreditCardInvoiceExpense $expense
 * @property-read \App\Models\User|null $shareUser
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag> $tags
 * @property-read int|null $tags_count
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpenseDivision newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpenseDivision newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpenseDivision query()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpenseDivision whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpenseDivision whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpenseDivision whereExpenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpenseDivision whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpenseDivision whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpenseDivision whereShareUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpenseDivision whereShareValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpenseDivision whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceExpenseDivision whereValue($value)
 * @mixin \Eloquent
 */
class CreditCardInvoiceExpenseDivision extends Model
{
    use HasFactory;

    protected $table = 'credit_card_invoice_expense_divisions';

    protected $fillable = [
        'description',
        'value',
        'remarks',
        'share_value',
        'expense_id',
        'share_user_id',
    ];

    public function shareUser(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'share_user_id');
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(CreditCardInvoiceExpense::class, 'expense_id', 'id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
