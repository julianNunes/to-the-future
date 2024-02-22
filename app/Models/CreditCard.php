<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\CreditCard
 *
 * @property int $id
 * @property string $name
 * @property string $digits
 * @property string $due_date Dia de Vencimento
 * @property string $closing_date Dia de fechamento
 * @property int $is_active
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CreditCardInvoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCard query()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCard whereClosingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCard whereDigits($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCard whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCard whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCard whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCard whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCard whereUserId($value)
 * @mixin \Eloquent
 */
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
