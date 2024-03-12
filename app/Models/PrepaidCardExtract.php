<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrepaidCardExtract extends Model
{
    use HasFactory;

    protected $table = 'prepaid_card_extracts';

    protected $fillable = [
        'year',
        'month',
        'balance',
        'remarks',
        'prepaid_card_id',
        'budget_id'
    ];

    public function prepaidCard(): BelongsTo
    {
        return $this->belongsTo(PrepaidCard::class, 'prepaid_card_id', 'id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(PrepaidCardExtractExpense::class, 'extract_id', 'id');
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class, 'budget_id', 'id');
    }
}
