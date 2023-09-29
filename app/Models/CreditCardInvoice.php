<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        return $this->belongsTo(CreditCard::class, 'id', 'credit_card_id');
    }
}
