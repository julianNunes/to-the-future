<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditCardInvoiceFile extends Model
{
    use HasFactory;

    protected $table = 'credit_card_invoice_files';

    protected $fillable = [
        'name',
        'path',
        'invoice_id',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(CreditCardInvoice::class, 'id', 'invoice_id');
    }
}
