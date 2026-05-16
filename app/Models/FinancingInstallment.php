<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperFinancingInstallment
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

    protected $casts = [
        'value' => 'decimal:2',
        'paid_value' => 'decimal:2',
        'portion' => 'integer',
        'date' => 'date:Y-m-d',
        'payment_date' => 'date:Y-m-d',
        'paid' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function financing(): BelongsTo
    {
        return $this->belongsTo(Financing::class, 'financing_id', 'id');
    }
}
