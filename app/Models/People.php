<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\People
 *
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property string $gender
 * @property string|null $address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\PeopleFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|People newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|People newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|People query()
 * @method static \Illuminate\Database\Eloquent\Builder|People whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|People whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|People whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|People whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|People whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|People whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|People wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|People whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class People extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'address',
    ];

    public function getCreatedAtAttribute($value)
    {
        return date('Y-m-d H:i:s', strtotime($value));
    }
}
