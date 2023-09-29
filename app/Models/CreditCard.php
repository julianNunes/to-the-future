<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CreditCard extends Model
{
    use HasFactory;

    protected $table = 'credit_cards';

    protected $fillable = [
        'name',
        'digits',
        'due_date',
        'closing_date',
        'is_active',
        'user_id',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(CreditCardInvoice::class, 'credit_card_id', 'id');
    }
}
