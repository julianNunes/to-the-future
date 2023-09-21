<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provision extends Model
{
    use HasFactory;

    protected $table = 'provisions';

    protected $fillable = [
        'description',
        'value',
        'week',
        'remarks',
        'share_percentage',
        'share_value',
        'share_user_id'
    ];
}
