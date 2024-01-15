<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
