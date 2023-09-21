<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserShare extends Model
{
    use HasFactory;

    protected $table = 'users_share';

    protected $fillable = [
        'user_id',
        'share_user_id',
    ];
}
