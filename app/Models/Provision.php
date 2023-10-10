<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Provision extends Model
{
    use HasFactory;

    protected $table = 'provisions';

    protected $fillable = [
        'description',
        'value',
        'group',
        'remarks',
        'share_percentage',
        'share_value',
        'share_user_id',
        'user_id'
    ];

    public function shareUser(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'share_user_id');
    }
}
