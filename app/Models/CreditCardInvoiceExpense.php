<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class CreditCardInvoiceExpense extends Model
{
    use HasFactory;

    protected $table = 'credit_card_invoice_expenses';

    protected $fillable = [
        'description',
        'date',
        'value',
        'portion',
        'portion_total',
        'remarks',
        'share_value',
        'credit_card_invoice_id',
        'share_user_id',
    ];

    public function shareUser(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'share_user_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(CreditCardInvoice::class, 'id', 'credit_card_invoice_id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
