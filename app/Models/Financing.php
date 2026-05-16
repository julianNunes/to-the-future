<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperFinancing
 */
class Financing extends Model
{
    use HasFactory;

    protected $table = 'financings';

    protected $fillable = [
        'description',
        'start_date',
        'total',
        'fees_monthly',
        'portion_total',
        'remarks',
        'user_id',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'total' => 'decimal:2',
        'fees_monthly' => 'decimal:2',
        'portion_total' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(FinancingInstallment::class, 'financing_id', 'id');
    }
}
