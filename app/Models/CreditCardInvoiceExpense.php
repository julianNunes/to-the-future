<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @mixin IdeHelperCreditCardInvoiceExpense
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
        'group_portion',
        'portion',
        'portion_total',
        'remarks',
        'share_value',
        'invoice_id',
        'share_user_id',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'value' => 'decimal:2',
        'group' => 'string',
        'group_portion' => 'string',
        'portion' => 'integer',
        'portion_total' => 'integer',
        'share_value' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function shareUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'share_user_id', 'id');
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
