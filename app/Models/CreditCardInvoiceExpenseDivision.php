<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class CreditCardInvoiceExpenseDivision extends Model
{
    use HasFactory;

    protected $table = 'credit_card_invoice_expense_divisions';

    protected $fillable = [
        'description',
        'value',
        'remarks',
        'share_value',
        'credit_card_invoice_expense_id',
        'share_user_id',
    ];

    public function shareUser(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'share_user_id');
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(CreditCardInvoiceExpense::class, 'id', 'credit_card_invoice_expense_id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
