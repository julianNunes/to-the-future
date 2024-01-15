<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(FinancingInstallment::class, 'financing_id', 'id');
    }
}
