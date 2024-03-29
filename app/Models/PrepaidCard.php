<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;


class PrepaidCard extends Model
{
    use HasFactory;

    protected $table = 'prepaid_cards';

    protected $fillable = [
        'name',
        'digits',
        'is_active',
        'user_id',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function extracts(): HasMany
    {
        return $this->hasMany(PrepaidCardExtract::class, 'prepaid_card_id', 'id');
    }
}
